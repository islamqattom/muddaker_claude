@props(['user' => null])

@if($user && ! $user->is_active)
    <span class="badge-inactive" title="حساب معطّل">(معطّل)</span>
@endif
