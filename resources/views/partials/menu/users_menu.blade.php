
@can('manage_users')
    <li class="{{ ($section=='users') ? 'navigation__active' : '' }}">
        <a href="{{route('users.home')}}"><i class="zmdi zmdi-account"></i> Users</a>
    </li>
@endcan
