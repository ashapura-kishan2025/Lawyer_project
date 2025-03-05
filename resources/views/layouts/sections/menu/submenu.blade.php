@php
    use Illuminate\Support\Facades\Route;
    use Illuminate\Support\Facades\DB;
    $user = auth()->user();

  // Get the user's role_id from the user_departments table
  $userRole = DB::table('users_departments')
      ->where('user_id', $user->id)
      ->first();  // Assuming the user has only one entry in the users_departments table
      // Check if the role_id is available

@endphp

<style>
    .bg-menu-theme .menu-toggle.active::after {
        color: #b08e78;
    }
</style>

<ul class="menu-sub">
    @if (isset($menu))
        @foreach ($menu as $submenu)
            {{-- active menu method --}}
            @php
                $activeClass = null;
                $active = $configData['layout'] === 'vertical' ? 'active open' : 'active';
                $currentRouteName = Route::currentRouteName();
            @endphp
            @if($userRole)
            {{-- Check if the menu item is "Permissions" and user role is not 2 --}}
              @if ($submenu->slug === 'permissions' && $userRole->role_id != 3)
                  @continue
              @endif
            @endif
            {{-- active menu method --}}
            @php
                $activeClass = null;
                $active = $configData["layout"] === 'vertical' ? 'active open' : 'active';
                $currentRouteName = Route::currentRouteName();

                if ($currentRouteName === $submenu->slug) {
                    $activeClass = 'active';
                }
                elseif (isset($submenu->submenu)) {
                    if (gettype($submenu->slug) === 'array') {
                        foreach($submenu->slug as $slug){
                            if (str_contains($currentRouteName, $slug) && strpos($currentRouteName, $slug) === 0) {
                                $activeClass = $active;
                            }
                        }
                    }
                    else{
                        if (str_contains($currentRouteName, $submenu->slug) && strpos($currentRouteName, $submenu->slug) === 0) {
                            $activeClass = $active;
                        }
                    }
                }
            @endphp

            <li class="menu-item {{$activeClass}}" data-slug="{{$submenu->slug}}">
                <a href="{{ isset($submenu->url) ? url($submenu->url) : 'javascript:void(0)' }}" class="{{ isset($submenu->submenu) ? 'menu-link menu-toggle' : 'menu-link' }}" @if (isset($submenu->target) and !empty($submenu->target)) target="_blank" @endif>
                    @if (isset($submenu->icon))
                        <i class="{{ $submenu->icon }}"></i>
                    @endif
                    <div>{{ isset($submenu->name) ? __($submenu->name) : '' }}</div>
                    @isset($submenu->badge)
                        <div class="badge bg-{{ $submenu->badge[0] }} rounded-pill ms-auto">{{ $submenu->badge[1] }}</div>
                    @endisset
                </a>

                {{-- submenu --}}
                @if (isset($submenu->submenu))
                    @include('layouts.sections.menu.submenu', ['menu' => $submenu->submenu])
                @endif
            </li>
        @endforeach
    @endif
</ul>
