@php
    $payload = $payload ?? [];
@endphp

<h2>Alerte sécurité</h2>

<p><strong>Type</strong> : {{ $type }}</p>

@if(!empty($payload))
    <h3>Détails</h3>
    <pre style="white-space: pre-wrap">{{ json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
@endif
