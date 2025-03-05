@php
    use Illuminate\Support\Facades\Route;
    $configData = Helper::appClasses();
@endphp


<script>
    $(document).ready(function() {
        var currentRoute = "{{ Route::currentRouteName() }}";
        var mainRoutePart = currentRoute.split('.')[0]; // Extract base route (before dot)

        var slugValue = $('.menu-item.active').data('slug');
        if (slugValue == "master") {
            var masterMenu = $('.menu-item[data-slug="master"]');
            masterMenu.removeClass("active");
            masterMenu.find('.menu-link.menu-toggle').addClass('active');
            masterMenu.find('.menu-link.menu-toggle.active::after').addClass('active');

            var submenu = $('.menu-item[data-slug="' + mainRoutePart + '"]').addClass('active');
            // submenu.addClass("active");

            // console.log(slugValue,mainRoutePart);
        }
        // Find the active submenu item
        // var activeSubmenu = $('.menu-item > a[href*="' + mainRoutePart + '"]').parent();

        // if (activeSubmenu.length > 0) { // Check if a submenu is found
        //     activeSubmenu.addClass('active'); // Add active class to submenu item

        //     // Find the parent menu (Master)
        //     var parentMenu = activeSubmenu.closest('ul.menu-sub').closest('li.menu-item');

        //     if (parentMenu.length > 0) { // Ensure parent menu exists
        //         parentMenu.removeClass('active'); // Activate the Master menu

        //         // Also activate the menu-toggle link inside Master
        //         parentMenu.find('.menu-link.menu-toggle').addClass('active');
        //     }
        // }
    });
</script>

<!-- Horizontal Menu -->
<aside id="layout-menu" class="layout-menu-horizontal menu-horizontal  menu bg-menu-theme flex-grow-0">
    <div class="{{ $containerNav }} d-flex h-100">
        <ul class="menu-inner pb-2 pb-xl-0">
            @foreach ($menuData[1]->menu as $menu)
                {{-- active menu method --}}
                @php
                    $activeClass = null;
                    $currentRouteName = Route::currentRouteName();
                    $parts = explode('.', $currentRouteName);

                    $mainPart = $parts[0]; // "client"

                    if ($mainPart === $menu->slug) {
                        $activeClass = 'active';
                    } elseif ($menu->slug === 'master' && !empty($menu->submenu)) {
                        foreach ($menu->submenu as $submenu) {
                            if ($mainPart === $submenu->slug) {
                                $activeClass = 'active';
                            }
                        }
                    }
                @endphp

                {{-- main menu --}}
                <li class="menu-item {{ $activeClass }}" data-slug="{{ $menu->slug }}">
                    <a href="{{ isset($menu->url) ? url($menu->url) : 'javascript:void(0);' }}"
                        class="{{ isset($menu->submenu) ? 'menu-link menu-toggle' : 'menu-link' }}"
                        @if (isset($menu->target) and !empty($menu->target)) target="_blank" @endif>
                        @isset($menu->icon)
                            <i class="{{ $menu->icon }}"></i>
                        @endisset
                        <div>{{ isset($menu->name) ? __($menu->name) : '' }}</div>
                    </a>


                    @isset($menu->submenu)
                        @include('layouts.sections.menu.submenu', ['menu' => $menu->submenu])
                    @endisset
                </li>
            @endforeach
        </ul>
    </div>
</aside>
<!--/ Horizontal Menu -->
