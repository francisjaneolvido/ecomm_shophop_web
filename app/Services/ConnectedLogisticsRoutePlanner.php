<?php
namespace App\Services;

use App\Models\Logistics\RouteLink;
use App\Models\Logistics\SortingCenter;
use Illuminate\Support\Facades\DB;

/** Planned transport connections, not a dispatch authorization or shipping quote. */
class ConnectedLogisticsRoutePlanner
{
    public function find(int $partnerId, int $fromId, int $toId, bool $hubOnly = false): array
    {
        $centers = SortingCenter::query()->where('logistics_partner_id',$partnerId)
            ->where('status','active')->whereNull('archived_at')
            ->when($hubOnly, fn($q)=>$q->where('type','province_hub'))
            ->get(['id','name','type','province','municipality','region'])->keyBy('id');
        if (! $centers->has($fromId) || ! $centers->has($toId)) {
            return ['status'=>'unavailable','stops'=>[],'legs'=>[],'note'=>'Origin or destination station is not active in this logistics network.'];
        }
        if ($fromId === $toId) {
            return ['status'=>'local','stops'=>[$this->stop($centers->get($fromId))], 'legs'=>[],
                'note'=>'Same sorting center. Confirm last-mile rider availability separately.'];
        }
        $edges = RouteLink::query()->where('logistics_partner_id',$partnerId)->where('status','verified')
            ->whereNotNull('verified_at')->get()->filter(fn ($e) => $centers->has((int)$e->from_center_id)
                && $centers->has((int)$e->to_center_id));
        $adj = [];
        $islands = app(PhilippineIslandGroup::class);
        foreach ($edges as $edge) {
            $from = $centers->get((int) $edge->from_center_id);
            $to = $centers->get((int) $edge->to_center_id);
            // Fail closed for missing PSGC region metadata when planning hub-to-hub links.
            if (! $islands->forCenter($from) || ! $islands->forCenter($to)) continue;
            if ($islands->isCrossGroup($from, $to) && ! in_array($edge->mode, ['sea','air'], true)) continue;
            $adj[(int) $edge->from_center_id][] = $edge;
        }
        $queue = [$fromId]; $seen = [$fromId=>true]; $parents=[];
        for ($i=0; $i<count($queue); $i++) {
            $id=$queue[$i];
            foreach ($adj[$id]??[] as $edge) {
                $next=(int)$edge->to_center_id;
                if (isset($seen[$next])) continue;
                $seen[$next]=true; $parents[$next]=[$id,$edge]; $queue[]=$next;
                if ($next===$toId) break 2;
            }
        }
        if (!isset($seen[$toId])) return ['status'=>'unavailable','stops'=>[], 'legs'=>[],
            'note'=>'No verified connected transport path. Add and verify real transport links before planning.'];
        $legs=[]; $nodes=[$toId]; $cursor=$toId;
        while ($cursor!==$fromId) {
            [$parent,$edge]=$parents[$cursor];
            $legs[]=['from_id'=>$parent,'to_id'=>$cursor,'mode'=>$edge->mode,'link_id'=>$edge->id];
            $nodes[]=$parent; $cursor=$parent;
        }
        $nodes=array_reverse($nodes); $legs=array_reverse($legs);
        return ['status'=>'connected','stops'=>array_map(fn($id)=>$this->stop($centers->get($id)),$nodes),
            'legs'=>$legs,'note'=>'Verified connectivity preview only; each transfer still requires a valid manifest, assigned linehaul and physical scan.'];
    }
    private function stop(SortingCenter $center): array
    {
        return ['id'=>(int)$center->id,'name'=>$center->name,'type'=>$center->type,
            'province'=>$center->province,'municipality'=>$center->municipality];
    }
}
