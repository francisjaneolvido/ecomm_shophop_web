<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Services\RegistrationDocuments;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class MigrateRegistrationDocuments extends Command
{
    // Explicit operator action only; dry-run inventories known columns without moving files or updating rows.
    protected $signature = 'registration-documents:migrate {--dry-run : Check references without changing files or rows}';
    protected $description = 'Move known registration documents from public storage into account-scoped private storage';

    public function handle(): int
    {
        // Enumerate profile records and known fields, never scan public storage or infer ownership from filenames.
        $failed = 0;
        $checked = 0;
        foreach (RegistrationDocuments::PROFILES as $role => [$model]) {
            $model::query()->orderBy('id')->each(function (Model $profile) use ($role, &$failed, &$checked) {
                foreach (RegistrationDocuments::SLOTS[$role] as $slot => [$field]) {
                    if (!$profile->{$field}) {
                        continue;
                    }
                    $checked++;
                    try {
                        $result = $this->migrate($profile, $role, $slot);
                        $this->line("{$role} profile {$profile->id} {$slot}: {$result}");
                    } catch (\DomainException $exception) {
                        $this->error("{$role} profile {$profile->id} {$slot}: ".$exception->getMessage());
                        $failed++;
                    } catch (\Throwable $exception) {
                        // Filesystem/database exceptions may expose host paths; report only the affected record/slot.
                        $this->error("{$role} profile {$profile->id} {$slot}: storage/database operation failed; rerun after investigation.");
                        $failed++;
                    }
                }
            });
        }
        $this->info("Checked {$checked} document reference(s); {$failed} failed. ".($this->option('dry-run') ? 'Dry run: no changes.' : 'Private copies verified before public removal.'));
        return $failed ? self::FAILURE : self::SUCCESS;
    }

    private function migrate(Model $profile, string $role, string $slot): string
    {
        [$field, , $legacyDirectory] = RegistrationDocuments::SLOTS[$role][$slot];
        $public = Storage::disk('public');
        $private = Storage::disk(RegistrationDocuments::DISK);

        // Lock and reread the authoritative profile so a stale command snapshot cannot rewrite another reference.
        [$source, $destination, $alreadyPrivate] = DB::transaction(function () use ($profile, $role, $slot, $field, $legacyDirectory, $public, $private) {
            $locked = $profile->newQuery()->lockForUpdate()->findOrFail($profile->id);
            $user = User::find($locked->user_id);
            if (!$user || $user->account_type !== $role) {
                throw new \DomainException('Inconsistent profile ownership; unchanged.');
            }
            $user->setRelation(RegistrationDocuments::PROFILES[$role][1], $locked);
            $path = $locked->{$field};
            $scoped = RegistrationDocuments::scopedPath($user, $slot);
            $alreadyPrivate = $scoped !== null;

            // Legacy input must be the exact historical role/slot directory and a single safe generated leaf.
            if ($alreadyPrivate) {
                $destination = $scoped;
                $source = $legacyDirectory.'/'.basename($scoped);
            } else {
                $prefix = $legacyDirectory.'/';
                if (!is_string($path) || !str_starts_with($path, $prefix)
                    || !RegistrationDocuments::safeFilename(substr($path, strlen($prefix)))) {
                    throw new \DomainException('Invalid legacy reference; unchanged.');
                }
                $source = $path;
                $destination = $user->id.'/'.$slot.'/'.basename($path);
            }

            // Ambiguous legacy references cannot be moved/deleted until their ownership is resolved by an operator.
            $references = $this->referenceCount($source);
            if ($references > ($alreadyPrivate ? 0 : 1)) {
                throw new \DomainException('Shared legacy reference; unchanged.');
            }
            if (!$public->exists($source)) {
                if ($alreadyPrivate && $private->exists($destination)) {
                    return [null, $destination, true];
                }
                throw new \DomainException('Missing source/private file; unchanged.');
            }
            if ($this->option('dry-run')) {
                return [$source, $destination, $alreadyPrivate];
            }

            // A copy is never trusted until reread bytes match; conflicts cannot overwrite a different private file.
            $contents = $public->get($source);
            if ($private->exists($destination)) {
                if (!hash_equals(hash('sha256', $contents), hash('sha256', $private->get($destination)))) {
                    throw new \DomainException('Private destination differs; unchanged.');
                }
            } elseif (!$private->put($destination, $contents)) {
                throw new \DomainException('Private write failed; public source retained.');
            }
            if (!hash_equals(hash('sha256', $contents), hash('sha256', $private->get($destination)))) {
                throw new \DomainException('Private verification failed; public source retained.');
            }
            if (!$alreadyPrivate) {
                $locked->{$field} = $destination;
                $locked->saveOrFail();
            }
            return [$source, $destination, $alreadyPrivate];
        });

        if ($this->option('dry-run')) {
            return $source ? 'would migrate/clean verified copy' : 'already private';
        }
        // Delete only after verified private bytes AND committed DB state. A failed delete is recoverable on rerun.
        if ($source !== null) {
            if ($this->referenceCount($source) !== 0 || !$public->delete($source) || $public->exists($source)) {
                throw new \DomainException('Public cleanup incomplete; private reference retained, rerun required.');
            }
        }
        return $alreadyPrivate ? 'already private / cleanup complete' : 'migrated';
    }

    private function referenceCount(string $path): int
    {
        // Count only exact known document columns; shared references are evidence of ambiguity, not permission to delete.
        $count = 0;
        foreach (RegistrationDocuments::PROFILES as $role => [$model]) {
            foreach (RegistrationDocuments::SLOTS[$role] as [$field]) {
                $count += $model::where($field, $path)->count();
            }
        }
        return $count;
    }
}
