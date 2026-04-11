<div class="sidebar" id="sidebar">
    <div class="sidebar-inner slimscroll">
        <div id="sidebar-menu" class="sidebar-menu">
            <ul>
                <li class="menu-title">
                    <span>Main</span>
                </li>
                <li class="{{ Route::is(['admin.dashboard']) ? 'active' : '' }}">
                    <a href="{{ route('admin.dashboard') }}">
                        <i class="fe fe-home"></i>
                        <span>
                            Dashboard
                        </span>
                    </a>
                </li>
                <li class="{{ Route::is(['admin.user.management', 'admin.user.profile']) ? 'active' : '' }}">
                    <a href="{{ route('admin.user.management') }}">
                        <i class="fa-solid fa-user"></i>
                        <span>
                            Users
                        </span>
                    </a>
                </li>
                </li>
                <li class="{{ Route::is(['admin.skills.management']) ? 'active' : '' }}">
                    <a href="{{ route('admin.skills.management') }}">
                        <i class="fa-solid fa-brain"></i>
                        <span>
                            Skills
                        </span>
                    </a>
                </li>
            </ul>
        </div>
    </div>
</div>
