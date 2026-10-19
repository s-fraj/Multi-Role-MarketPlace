<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/dist/tabler-icons.min.css">
<x-marketplace-layout title="Souk — Notifications">

  <div class="app">
    @include('profile.vendeur.partials.sidebar', ['active' => 'notifications'])

    <main class="main">
      <h1 class="page-title">Notifications</h1>
      <div class="page-sub">Décisions de modération et activité récente sur tes produits</div>

      <div class="panel" style="padding: 0; margin-top: 20px;">
        @forelse ($notifications as $notification)
          @php $lien = $notification->lien(); $tag = $lien ? 'a' : 'div'; @endphp
          <{{ $tag }} @if($lien) href="{{ $lien }}" @endif class="moderation-card" style="align-items: center; text-decoration:none; color:inherit; {{ $lien ? 'cursor:pointer;' : '' }} {{ ! $notification->lu ? 'background: rgba(30,92,99,0.04);' : '' }}">
            <div style="width:40px; height:40px; border-radius:50%; background: var(--paper); display:flex; align-items:center; justify-content:center; flex-shrink:0;">
              <i class="ti {{ $notification->icone() }}" style="font-size:20px; color: {{ in_array($notification->type, ['produit_refuse', 'drone_erreur']) ? 'var(--terracotta)' : '#1E6B3A' }};"></i>
            </div>
            <div class="moderation-info">
              <div class="nom" style="font-size:14px;">{{ $notification->message }}</div>
              <div class="meta">{{ $notification->created_at->diffForHumans() }}</div>
            </div>
            @if ($lien)
              <span style="white-space:nowrap; font-size:13px; font-weight:600; color: var(--teal);">
                {{ in_array($notification->type, ['nouvel_avis', 'avis_modifie']) ? "Voir l'avis" : 'Voir mes produits' }} <i class="ti ti-chevron-right"></i>
              </span>
            @endif
          </{{ $tag }}>
        @empty
          <div class="empty-state">
            <i class="ti ti-bell-off"></i>
            Aucune notification pour le moment.
          </div>
        @endforelse
      </div>
    </main>
  </div>

</x-marketplace-layout >
