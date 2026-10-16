<?php
namespace App\Models\Finance;
use Illuminate\Database\Eloquent\Model;
class PaymentSetting extends Model
{
    protected $table = 'shophop_payment_settings';
    protected $fillable = ['mode', 'recipient_name', 'recipient_identifier', 'qr_path', 'instructions'];
    public static function current(): ?self { return self::find(1); }
    public function qrUrl(): ?string
    {
        return $this->qr_path ? \Illuminate\Support\Facades\Storage::disk('public')->url($this->qr_path) : null;
    }
    public function publicDetails(): array
    {
        return ['mode' => $this->mode, 'recipient_name' => $this->recipient_name,
            'recipient_identifier' => $this->recipient_identifier, 'instructions' => $this->instructions,
            'qr_url' => $this->qrUrl(), 'is_demo' => $this->mode === 'demo'];
    }
}
