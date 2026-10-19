@php
  /** @var \App\Models\Avis $avisItem */
@endphp
<div class="avis-item">
  <div class="avis-header">
    <div class="avis-avatar">
      @if ($avisItem->user->avatarUrl())
        <img src="{{ $avisItem->user->avatarUrl() }}" alt="">
      @else
        {{ $avisItem->user->initials() }}
      @endif
    </div>
    <div class="avis-header-text">
      <div class="avis-author">{{ $avisItem->user->name }}</div>
      <div class="avis-date">{{ $avisItem->created_at->translatedFormat('j M Y') }}</div>
    </div>
    <div class="avis-stars">
      @for ($i = 1; $i <= 5; $i++)
        <i class="ti ti-star{{ $i <= $avisItem->note ? ' filled' : '' }}"></i>
      @endfor
    </div>
  </div>

  <p class="avis-comment">{{ $avisItem->commentaire }}</p>

  @if ($avisItem->photos->isNotEmpty())
    <div class="avis-photos">
      @foreach ($avisItem->photos as $photo)
        <a href="{{ $photo->url() }}" target="_blank" rel="noopener" class="avis-photo" style="background-image:url('{{ $photo->url() }}');"></a>
      @endforeach
    </div>
  @endif
</div>
