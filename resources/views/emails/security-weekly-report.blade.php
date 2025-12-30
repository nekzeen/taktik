<h2>Rapport sécurité hebdomadaire</h2>

<h3>Checks</h3>
<ul>
@foreach(($checks ?? []) as $check)
    <li>
        <strong>{{ $check['key'] ?? '' }}</strong>
        - attendu: <code>{{ is_bool($check['expected'] ?? null) ? (($check['expected'] ?? false) ? 'true' : 'false') : ($check['expected'] ?? '') }}</code>
        - actuel: <code>{{ is_bool($check['actual'] ?? null) ? (($check['actual'] ?? false) ? 'true' : 'false') : ($check['actual'] ?? '') }}</code>
    </li>
@endforeach
</ul>

<h3>Commandes de maintenance recommandées</h3>
<ul>
@foreach(($commands ?? []) as $cmd)
    <li>
        <strong>{{ $cmd['title'] ?? '' }}</strong><br>
        <code>{{ $cmd['command'] ?? '' }}</code><br>
        @if(!empty($cmd['notes']))
            <span>{{ $cmd['notes'] }}</span>
        @endif
    </li>
@endforeach
</ul>
