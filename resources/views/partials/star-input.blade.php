@php
  // Expected: $suffix (unique id fragment, e.g. 'new' or 'edit'), $valeurInitiale (int 0-5)
  $valeurInitiale = $valeurInitiale ?? 0;
@endphp
<div class="star-input" id="star-input-{{ $suffix }}">
  @for ($i = 1; $i <= 5; $i++)
    <i class="ti ti-star{{ $i <= $valeurInitiale ? ' filled' : '' }}" data-valeur="{{ $i }}"></i>
  @endfor
</div>
<input type="hidden" name="note" id="note-input-{{ $suffix }}" value="{{ $valeurInitiale }}">
