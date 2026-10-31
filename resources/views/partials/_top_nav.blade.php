<nav class="navbar navbar-light">
    <div class="navbar-left">
        <div class="logo-area">
            @php
                $clinicSettings = \App\Models\ClinicSetting::first();
                $clinicFullName = $clinicSettings->name ?? 'Klinik';
                // Ambil 2 kata pertama untuk logo
                $words = explode(' ', $clinicFullName);
                $clinicName = implode(' ', array_slice($words, 0, 2));
            @endphp
            <a class="navbar-brand" href="{{ route('clinic.dashboard', app()->getLocale()) }}"
                style="text-decoration: none;">
                <span style="display: flex; align-items: center; gap: 8px;">
                    <i class="uil uil-medical-square" style="font-size: 32px; color: #01B574;"></i>
                    <span style="font-size: 20px; font-weight: 700; color: #01B574;">{{ $clinicName }}</span>
                </span>
            </a>
            <a href="#" class="sidebar-toggle">
                <img class="svg" src="{{ asset('assets/img/svg/align-center-alt.svg') }}" alt="img"></a>
        </div>

        <div class="top-menu">
            <div class="hexadash-top-menu position-relative">
                <ul>
                    <li class="has-subMenu">
                        <a href="#"
                            class="{{ Request::is(app()->getLocale() . '/dashboards/*') ? 'active' : '' }}">Dashboard</a>
                        <ul class="subMenu">
                            <li><a class="{{ Request::is(app()->getLocale() . '/dashboards/demo-one') ? 'active' : '' }}"
                                    href="{{ route('dashboard.demo_one', app()->getLocale()) }}">{{ trans('menu.dashboard-demo-one') }}</a>
                            </li>
                            <li><a class="{{ Request::is(app()->getLocale() . '/dashboards/demo-two') ? 'active' : '' }}"
                                    href="{{ route('dashboard.demo_two', app()->getLocale()) }}">{{ trans('menu.dashboard-demo-two') }}</a>
                            </li>
                            <li><a class="{{ Request::is(app()->getLocale() . '/dashboards/demo-three') ? 'active' : '' }}"
                                    href="{{ route('dashboard.demo_three', app()->getLocale()) }}">{{ trans('menu.dashboard-demo-three') }}</a>
                            </li>
                            <li><a class="{{ Request::is(app()->getLocale() . '/dashboards/demo-four') ? 'active' : '' }}"
                                    href="{{ route('dashboard.demo_four', app()->getLocale()) }}">{{ trans('menu.dashboard-demo-four') }}</a>
                            </li>
                            <li><a class="{{ Request::is(app()->getLocale() . '/dashboards/demo-five') ? 'active' : '' }}"
                                    href="{{ route('dashboard.demo_five', app()->getLocale()) }}">{{ trans('menu.dashboard-demo-five') }}</a>
                            </li>
                            <li><a class="{{ Request::is(app()->getLocale() . '/dashboards/demo-six') ? 'active' : '' }}"
                                    href="{{ route('dashboard.demo_six', app()->getLocale()) }}">{{ trans('menu.dashboard-demo-six') }}</a>
                            </li>
                            <li><a class="{{ Request::is(app()->getLocale() . '/dashboards/demo-seven') ? 'active' : '' }}"
                                    href="{{ route('dashboard.demo_seven', app()->getLocale()) }}">{{ trans('menu.dashboard-demo-seven') }}</a>
                            </li>
                            <li><a class="{{ Request::is(app()->getLocale() . '/dashboards/demo-eight') ? 'active' : '' }}"
                                    href="{{ route('dashboard.demo_eight', app()->getLocale()) }}">{{ trans('menu.dashboard-demo-eight') }}</a>
                            </li>
                            <li><a class="{{ Request::is(app()->getLocale() . '/dashboards/demo-nine') ? 'active' : '' }}"
                                    href="{{ route('dashboard.demo_nine', app()->getLocale()) }}">{{ trans('menu.dashboard-demo-nine') }}</a>
                            </li>
                            <li><a class="{{ Request::is(app()->getLocale() . '/dashboards/demo-ten') ? 'active' : '' }}"
                                    href="{{ route('dashboard.demo_ten', app()->getLocale()) }}">{{ trans('menu.dashboard-demo-ten') }}</a>
                            </li>
                        </ul>
                    </li>
                    <li class="has-subMenu">
                        <a href="#" class="">{{ trans('menu.layout-menu-title') }}</a>
                        <ul class="subMenu">
                            <li class="l_sidebar">
                                <a href="#" data-layout="light">{{ trans('menu.layout-light-mode') }}</a>
                            </li>
                            <li class="l_sidebar">
                                <a href="#" data-layout="dark">{{ trans('menu.layout-dark-mode') }}</a>
                            </li>
                            <li class="l_navbar">
                                <a href="#" data-layout="top">{{ trans('menu.layout-top-menu') }}</a>
                            </li>
                            <li class="layout">
                                <a href="{{ Helper::get_translation_url('ar') }}">{{ trans('menu.layout-rtl') }}</a>
                            </li>
                            <li class="layout">
                                <a href="{{ Helper::get_translation_url('en') }}">{{ trans('menu.layout-ltr') }}</a>
                            </li>
                        </ul>
                    </li>
                    <li class="has-subMenu">
                        <a href="#"
                            class="{{ Request::is(app()->getLocale() . '/table/*') || Request::is(app()->getLocale() . '/pages/dynamic-table') || Request::is(app()->getLocale() . '/applications/job/*') || Request::is(app()->getLocale() . '/applications/support/*') || Request::is(app()->getLocale() . '/applications/social/profile-settings') || Request::is(app()->getLocale() . '/applications/bookmark') || Request::is(app()->getLocale() . '/applications/task') || Request::is(app()->getLocale() . '/applications/filemanager') || Request::is(app()->getLocale() . '/applications/import_export/*') || Request::is(app()->getLocale() . '/applications/kanban') || Request::is(app()->getLocale() . '/applications/todo') || Request::is(app()->getLocale() . '/applications/note') || Request::is(app()->getLocale() . '/applications/contact/*') || Request::is(app()->getLocale() . '/applications/user/*') || Request::is(app()->getLocale() . '/applications/calendar') || Request::is(app()->getLocale() . '/applications/project/*') || Request::is(app()->getLocale() . '/applications/ecommerce/*') || Request::is(app()->getLocale() . '/applications/email/*') || Request::is(app()->getLocale() . '/applications/chat') ? 'active' : '' }}">Apps</a>
                        <ul class="megaMenu-wrapper megaMenu-dropdown">
                            <li>
                                <ul>
                                    <li class="has-subMenu-left">
                                        <a href="#"
                                            class="{{ Request::is(app()->getLocale() . '/applications/email/*') ? 'active' : '' }}">
                                            <span class="nav-icon uil uil-envelope"></span>
                                            <span class="menu-text">{{ trans('menu.email-menu-title') }}</span>
                                        </a>
                                        <ul class="subMenu">
                                            <li><a class="{{ Request::is(app()->getLocale() . '/applications/email/inbox') ? 'active' : '' }}"
                                                    href="{{ route('email.inbox', app()->getLocale()) }}">{{ trans('menu.email-inbox') }}</a>
                                            </li>
                                            <li><a class="{{ Request::is(app()->getLocale() . '/applications/email/read') ? 'active' : '' }}"
                                                    href="{{ route('email.read', app()->getLocale()) }}">{{ trans('menu.email-read') }}</a>
                                            </li>
                                        </ul>
                                    </li>
                                    <li>
                                        <a href="{{ route('chat', app()->getLocale()) }}"
                                            class="{{ Request::is(app()->getLocale() . '/applications/chat') ? 'active' : '' }}">
                                            <span class="nav-icon uil uil-chat"></span>
                                            <span class="menu-text">{{ trans('menu.chat-menu-title') }}</span>
                                            <span class="badge badge-success menuItem rounded-circle">3</span>
                                        </a>
                                    </li>
                                    <li class="has-subMenu-left">
                                        <a href="#"
                                            class="{{ Request::is(app()->getLocale() . '/applications/ecommerce/*') ? 'active' : '' }}">
                                            <span class="nav-icon uil uil-bag"></span>
                                            <span
                                                class="menu-text text-initial">{{ trans('menu.ecommerce-menu-title') }}</span>
                                        </a>
                                        <ul class="subMenu">
                                            <li><a href="{{ route('ecommerce.products', app()->getLocale()) }}"
                                                    class="{{ Request::is(app()->getLocale() . '/applications/ecommerce/products') ? 'active' : '' }}">{{ trans('menu.ecommerce-products') }}</a>
                                            </li>
                                            <li><a href="{{ route('ecommerce.product_detail', app()->getLocale()) }}"
                                                    class="{{ Request::is(app()->getLocale() . '/applications/ecommerce/product-detail') ? 'active' : '' }}">{{ trans('menu.ecommerce-product-details') }}</a>
                                            </li>
                                            <li><a href="{{ route('ecommerce.add_product', app()->getLocale()) }}"
                                                    class="{{ Request::is(app()->getLocale() . '/applications/ecommerce/add-product') ? 'active' : '' }}">{{ trans('menu.ecommerce-product-add') }}</a>
                                            </li>
                                            <li><a href="{{ route('ecommerce.cart', app()->getLocale()) }}"
                                                    class="{{ Request::is(app()->getLocale() . '/applications/ecommerce/cart') ? 'active' : '' }}">{{ trans('menu.ecommerce-cart') }}</a>
                                            </li>
                                            <li><a href="{{ route('ecommerce.orders', app()->getLocale()) }}"
                                                    class="{{ Request::is(app()->getLocale() . '/applications/ecommerce/orders') ? 'active' : '' }}">{{ trans('menu.ecommerce-orders') }}</a>
                                            </li>
                                            <li><a href="{{ route('ecommerce.sellers', app()->getLocale()) }}"
                                                    class="{{ Request::is(app()->getLocale() . '/applications/ecommerce/sellers') ? 'active' : '' }}">{{ trans('menu.ecommerce-sellers') }}</a>
                                            </li>
                                            <li><a href="{{ route('ecommerce.invoice', app()->getLocale()) }}"
                                                    class="{{ Request::is(app()->getLocale() . '/applications/ecommerce/invoice') ? 'active' : '' }}">{{ trans('menu.ecommerce-invoices') }}</a>
                                            </li>
                                        </ul>
                                    </li>
                                    <li class="has-subMenu-left">
                                        <a href="#"
                                            class="{{ Request::is(app()->getLocale() . '/applications/project/*') ? 'active' : '' }}">
                                            <span class="nav-icon uil uil-folder"></span>
                                            <span class="menu-text">{{ trans('menu.project-menu-title') }}</span>
                                        </a>
                                        <ul class="subMenu">
                                            <li><a href="{{ route('project.project_list', app()->getLocale()) }}"
                                                    class="{{ Request::is(app()->getLocale() . '/applications/project/list') ? 'active' : '' }}">{{ trans('menu.project-title') }}</a>
                                            </li>
                                            <li><a href="{{ route('project.project_detail', app()->getLocale()) }}"
                                                    class="{{ Request::is(app()->getLocale() . '/applications/project/project-detail') ? 'active' : '' }}">{{ trans('menu.project-detail') }}</a>
                                            </li>
                                            <li><a href="{{ route('project.create_project', app()->getLocale()) }}"
                                                    class="{{ Request::is(app()->getLocale() . '/applications/project/create') ? 'active' : '' }}">{{ trans('menu.create-project') }}</a>
                                            </li>
                                        </ul>
                                    </li>
                                    <li>
                                        <a href="{{ route('calendar', app()->getLocale()) }}"
                                            class="{{ Request::is(app()->getLocale() . '/applications/calendar') ? 'active' : '' }}">
                                            <span class="nav-icon uil uil-calendar-alt"></span>
                                            <span class="menu-text">{{ trans('menu.calendar-menu-title') }}</span>
                                        </a>
                                    </li>
                                    <li class="has-subMenu-left">
                                        <a href="#"
                                            class="{{ Request::is(app()->getLocale() . '/applications/user/*') ? 'active' : '' }}">
                                            <span class="nav-icon uil uil-users-alt"></span>
                                            <span class="menu-text">{{ trans('menu.user-menu-title') }}</span>
                                        </a>
                                        <ul class="subMenu">
                                            <li><a href="{{ route('user.member', app()->getLocale()) }}"
                                                    class="{{ Request::is(app()->getLocale() . '/applications/user/member') ? 'active' : '' }}">{{ trans('menu.user-team') }}</a>
                                            </li>
                                            <li><a href="{{ route('user.grid', app()->getLocale()) }}"
                                                    class="{{ Request::is(app()->getLocale() . '/applications/user/grid') ? 'active' : '' }}">{{ trans('menu.user-grid') }}</a>
                                            </li>
                                            <li><a href="{{ route('user.list', app()->getLocale()) }}"
                                                    class="{{ Request::is(app()->getLocale() . '/applications/user/list') ? 'active' : '' }}">{{ trans('menu.user-list') }}</a>
                                            </li>
                                            <li><a href="{{ route('user.grid_style', app()->getLocale()) }}"
                                                    class="{{ Request::is(app()->getLocale() . '/applications/user/grid-style') ? 'active' : '' }}">{{ trans('menu.user-grid-style') }}</a>
                                            </li>
                                            <li><a href="{{ route('user.group', app()->getLocale()) }}"
                                                    class="{{ Request::is(app()->getLocale() . '/applications/user/group') ? 'active' : '' }}">{{ trans('menu.user-group') }}</a>
                                            </li>
                                            <li><a href="{{ route('user.add', app()->getLocale()) }}"
                                                    class="{{ Request::is(app()->getLocale() . '/applications/user/add') ? 'active' : '' }}">{{ trans('menu.user-add') }}</a>
                                            </li>
                                            <li><a href="{{ route('user.table', app()->getLocale()) }}"
                                                    class="{{ Request::is(app()->getLocale() . '/applications/user/table') ? 'active' : '' }}">{{ trans('menu.user-table') }}</a>
                                            </li>
                                        </ul>
                                    </li>
                                    <li class="has-subMenu-left">
                                        <a href="#"
                                            class="{{ Request::is(app()->getLocale() . '/applications/contact/*') ? 'active' : '' }}">
                                            <span class="nav-icon uil uil-at"></span>
                                            <span class="menu-text">{{ trans('menu.contact-menu-title') }}</span>
                                        </a>
                                        <ul class="subMenu">
                                            <li><a class="{{ Request::is(app()->getLocale() . '/applications/contact/grid') ? 'active' : '' }}"
                                                    href="{{ route('contact.grid', app()->getLocale()) }}">{{ trans('menu.contact-grid') }}</a>
                                            </li>
                                            <li><a class="{{ Request::is(app()->getLocale() . '/applications/contact/list') ? 'active' : '' }}"
                                                    href="{{ route('contact.list', app()->getLocale()) }}">{{ trans('menu.contact-list') }}</a>
                                            </li>
                                            <li><a class="{{ Request::is(app()->getLocale() . '/applications/contact/create') ? 'active' : '' }}"
                                                    href="{{ route('contact.create', app()->getLocale()) }}">{{ trans('menu.contact-list') }}</a>
                                            </li>
                                        </ul>
                                    </li>
                                    <li>
                                        <a href="{{ route('note', app()->getLocale()) }}"
                                            class="{{ Request::is(app()->getLocale() . '/applications/note') ? 'active' : '' }}">
                                            <span class="nav-icon uil uil-clipboard-notes"></span>
                                            <span class="menu-text">{{ trans('menu.note-menu-title') }}</span>
                                        </a>
                                    </li>
                                    <li>
                                        <a href="{{ route('todo', app()->getLocale()) }}"
                                            class="{{ Request::is(app()->getLocale() . '/applications/todo') ? 'active' : '' }}">
                                            <span class="nav-icon uil uil-check-square"></span>
                                            <span class="menu-text">{{ trans('menu.todo-menu-title') }}</span>
                                        </a>
                                    </li>
                                </ul>
                            </li>
                            <li>
                                <ul>
                                    <li>
                                        <a href="{{ route('kanban', app()->getLocale()) }}"
                                            class="{{ Request::is(app()->getLocale() . '/applications/kanban') ? 'active' : '' }}">
                                            <span class="nav-icon uil uil-expand-arrows"></span>
                                            <span class="menu-text">{{ trans('menu.kanban-menu-title') }}</span>
                                        </a>
                                    </li>
                                    <li class="has-subMenu-left">
                                        <a href="#"
                                            class="{{ Request::is(app()->getLocale() . '/applications/import_export/*') ? 'active' : '' }}">
                                            <span class="nav-icon uil uil-exchange"></span>
                                            <span class="menu-text">{{ trans('menu.ie-menu-title') }}</span>
                                        </a>
                                        <ul class="subMenu">
                                            <li><a class="{{ Request::is(app()->getLocale() . '/applications/import_export/import') ? 'active' : '' }}"
                                                    href="{{ route('import_export.import', app()->getLocale()) }}">{{ trans('menu.ie-import') }}</a>
                                            </li>
                                            <li><a class="{{ Request::is(app()->getLocale() . '/applications/import_export/export') ? 'active' : '' }}"
                                                    href="{{ route('import_export.export', app()->getLocale()) }}">{{ trans('menu.ie-export') }}</a>
                                            </li>
                                            <li><a class="{{ Request::is(app()->getLocale() . '/applications/import_export/export-selected') ? 'active' : '' }}"
                                                    href="{{ route('import_export.export_selected', app()->getLocale()) }}">{{ trans('menu.ie-export-selected') }}</a>
                                            </li>
                                        </ul>
                                    </li>
                                    <li>
                                        <a href="{{ route('filemanager', app()->getLocale()) }}"
                                            class="{{ Request::is(app()->getLocale() . '/applications/filemanager') ? 'active' : '' }}">
                                            <span class="nav-icon uil uil-repeat"></span>
                                            <span class="menu-text">{{ trans('menu.filemanager-menu-title') }}</span>
                                        </a>
                                    </li>
                                    <li>
                                        <a href="{{ route('task', app()->getLocale()) }}"
                                            class="{{ Request::is(app()->getLocale() . '/applications/task') ? 'active' : '' }}">
                                            <span class="nav-icon uil uil-lightbulb-alt"></span>
                                            <span class="menu-text">{{ trans('menu.task-menu-title') }}</span>
                                        </a>
                                    </li>
                                    <li>
                                        <a href="{{ route('bookmark', app()->getLocale()) }}"
                                            class="{{ Request::is(app()->getLocale() . '/applications/bookmark') ? 'active' : '' }}">
                                            <span class="nav-icon uil uil-bookmark"></span>
                                            <span class="menu-text">{{ trans('menu.bookmark-menu-title') }}</span>
                                        </a>
                                    </li>
                                    <li class="has-subMenu-left">
                                        <a href="#"
                                            class="{{ Request::is(app()->getLocale() . '/applications/social/*') ? 'active' : '' }}">
                                            <span class="nav-icon uil uil-apps"></span>
                                            <span class="menu-text">{{ trans('menu.social-menu-title') }}</span>
                                        </a>
                                        <ul class="subMenu">
                                            <li class="nav-item"><a
                                                    href="{{ route('social.profile', app()->getLocale()) }}"
                                                    class="{{ Request::is(app()->getLocale() . '/applications/social/profile') ? 'active' : '' }}">{{ trans('menu.social-profile') }}</a>
                                            </li>
                                            <li><a href="{{ route('social.profile_settings', app()->getLocale()) }}"
                                                    class="{{ Request::is(app()->getLocale() . '/applications/social/profile-settings') ? 'active' : '' }}">{{ trans('menu.social-profile-setting') }}</a>
                                            </li>
                                        </ul>
                                    </li>
                                    <li class="has-subMenu-left">
                                        <a href="#"
                                            class="{{ Request::is(app()->getLocale() . '/applications/support/*') ? 'active' : '' }}">
                                            <span class="nav-icon uil uil-user"></span>
                                            <span class="menu-text">{{ trans('menu.support-menu-title') }}</span>
                                        </a>
                                        <ul class="subMenu">
                                            <li><a class="{{ Request::is(app()->getLocale() . '/applications/support/support-ticket') ? 'active' : '' }}"
                                                    href="{{ route('support.support_ticket', app()->getLocale()) }}">{{ trans('menu.support-ticket') }}</a>
                                            </li>
                                            <li><a class="{{ Request::is(app()->getLocale() . '/applications/support/support-details') ? 'active' : '' }}"
                                                    href="{{ route('support.support_detail', app()->getLocale()) }}">{{ trans('menu.support-ticket-detail') }}</a>
                                            </li>
                                            <li><a class="{{ Request::is(app()->getLocale() . '/applications/support/new-ticket') ? 'active' : '' }}"
                                                    href="{{ route('support.new_ticket', app()->getLocale()) }}">{{ trans('menu.support-new-ticket') }}</a>
                                            </li>
                                        </ul>
                                    </li>
                                    <li class="has-subMenu-left">
                                        <a href="#"
                                            class="{{ Request::is(app()->getLocale() . '/applications/job/*') ? 'active' : '' }}">
                                            <span class="nav-icon uil uil-search"></span>
                                            <span class="menu-text">{{ trans('menu.job-menu-title') }}</span>
                                        </a>
                                        <ul class="subMenu">
                                            <li><a class="{{ Request::is(app()->getLocale() . '/applications/job/job-search') ? 'active' : '' }}"
                                                    href="{{ route('job.job_search', app()->getLocale()) }}">{{ trans('menu.job-search') }}</a>
                                            </li>
                                            <li><a class="{{ Request::is(app()->getLocale() . '/applications/job/job-search-list') ? 'active' : '' }}"
                                                    href="{{ route('job.job_search_list', app()->getLocale()) }}">{{ trans('menu.job-search-list') }}</a>
                                            </li>
                                            <li><a class="{{ Request::is(app()->getLocale() . '/applications/job/job-detail') ? 'active' : '' }}"
                                                    href="{{ route('job.job_detail', app()->getLocale()) }}">{{ trans('menu.job-detail') }}</a>
                                            </li>
                                            <li><a class="{{ Request::is(app()->getLocale() . '/applications/job/job-apply') ? 'active' : '' }}"
                                                    href="{{ route('job.job_apply', app()->getLocale()) }}">{{ trans('menu.job-apply') }}</a>
                                            </li>
                                        </ul>
                                    </li>
                                    <li class="has-subMenu-left">
                                        <a href="#"
                                            class="{{ Request::is(app()->getLocale() . '/table/*') || Request::is(app()->getLocale() . '/pages/dynamic-table') ? 'active' : '' }}">
                                            <span class="nav-icon uil uil-table"></span>
                                            <span class="menu-text">{{ trans('menu.table-menu-title') }}</span>
                                        </a>
                                        <ul class="subMenu">
                                            <li><a href="{{ route('table.basic', app()->getLocale()) }}"
                                                    class="{{ Request::is(app()->getLocale() . '/table/basic') ? 'active' : '' }}">{{ trans('menu.table-basic') }}</a>
                                            </li>
                                            <li><a href="{{ route('table.data', app()->getLocale()) }}"
                                                    class="{{ Request::is(app()->getLocale() . '/table/data') ? 'active' : '' }}">{{ trans('menu.table-data') }}</a>
                                            </li>
                                            <li><a href="{{ route('pages.dynamic_table', app()->getLocale()) }}"
                                                    class="{{ Request::is(app()->getLocale() . '/pages/dynamic-table') ? 'active' : '' }}">{{ trans('menu.dynamic-table-menu-title') }}</a>
                                            </li>
                                        </ul>
                                    </li>
                                </ul>
                            </li>
                        </ul>
                    </li>
                    <li class="has-subMenu">
                        <a href="#"
                            class="{{ Request::is(app()->getLocale() . '/customer/*') ? 'active' : '' }}">Crud</a>
                        <ul class="subMenu">
                            <li class="has-subMenu-left">
                                <a href="#"
                                    class="{{ Request::is(app()->getLocale() . '/customer/*') ? 'active' : '' }}">
                                    <span class="nav-icon uil uil-database"></span>
                                    <span class="menu-text">{{ trans('menu.customer-crud-menu-title') }}</span>
                                </a>
                                <ul class="subMenu">
                                    <li><a class="{{ Request::is(app()->getLocale() . '/customer/list') ? 'active' : '' }}"
                                            href="{{ route('customer.list', app()->getLocale()) }}">{{ trans('menu.customer-view-all') }}</a>
                                    </li>
                                    <li><a class="{{ Request::is(app()->getLocale() . '/customer/create') ? 'active' : '' }}"
                                            href="{{ route('customer.create', app()->getLocale()) }}">{{ trans('menu.customer-add-new') }}</a>
                                    </li>
                                </ul>
                            </li>
                        </ul>
                    </li>
                    <li class="has-subMenu">
                        <a href="#"
                            class="{{ Request::is(app()->getLocale() . '/wizard/*') || Request::is(app()->getLocale() . '/widget/*') || Request::is(app()->getLocale() . '/map/*') || Request::is(app()->getLocale() . '/form/*') || Request::is(app()->getLocale() . '/chart/*') || Request::is(app()->getLocale() . '/editor') || Request::is(app()->getLocale() . '/icon/*') ? 'active' : '' }}">Features</a>
                        <ul class="subMenu">
                            <li>
                                <a href="{{ route('editor', app()->getLocale()) }}"
                                    class="{{ Request::is(app()->getLocale() . '/editor') ? 'active' : '' }}">
                                    <span class="nav-icon uil uil-edit"></span>
                                    <span class="menu-text">{{ trans('menu.editor-menu-title') }}</span>
                                </a>
                            </li>
                            <li class="has-subMenu-left">
                                <a href="#" class="{{ Request::is(app()->getLocale() . '/icon/*') ? 'active' : '' }}">
                                    <span class="nav-icon uil uil-grid"></span>
                                    <span class="menu-text">{{ trans('menu.icon-menu-title') }}</span>
                                </a>
                                <ul class="subMenu">
                                    <li><a href="{{ route('icon.unicon', app()->getLocale()) }}"
                                            class="{{ Request::is(app()->getLocale() . '/icon/unicon') ? 'active' : '' }}">{{ trans('menu.icon-unicon') }}</a>
                                    </li>
                                    <li><a href="{{ route('icon.awesome', app()->getLocale()) }}"
                                            class="{{ Request::is(app()->getLocale() . '/icon/awesome') ? 'active' : '' }}">{{ trans('menu.icon-awesome') }}</a>
                                    </li>
                                    <li><a href="{{ route('icon.lineawesome', app()->getLocale()) }}"
                                            class="{{ Request::is(app()->getLocale() . '/icon/lineawesome') ? 'active' : '' }}">{{ trans('menu.icon-line') }}</a>
                                    </li>
                                </ul>
                            </li>
                            <li class="has-subMenu-left">
                                <a href="#" class="{{ Request::is(app()->getLocale() . '/chart/*') ? 'active' : '' }}">
                                    <span class="nav-icon uil uil-chart"></span>
                                    <span class="menu-text">{{ trans('menu.chart-menu-title') }}</span>
                                </a>
                                <ul class="subMenu">
                                    <li><a class="{{ Request::is(app()->getLocale() . '/chart/chartjs') ? 'active' : '' }}"
                                            href="{{ route('chart.chartjs', app()->getLocale()) }}">{{ trans('menu.chart-js') }}</a>
                                    </li>
                                    <li><a class="{{ Request::is(app()->getLocale() . '/chart/google') ? 'active' : '' }}"
                                            href="{{ route('chart.google', app()->getLocale()) }}">{{ trans('menu.chart-google') }}</a>
                                    </li>
                                    <li><a class="{{ Request::is(app()->getLocale() . '/chart/peity') ? 'active' : '' }}"
                                            href="{{ route('chart.peity', app()->getLocale()) }}">{{ trans('menu.chart-peity') }}</a>
                                    </li>
                                </ul>
                            </li>
                            <li class="has-subMenu-left">
                                <a href="#" class="{{ Request::is(app()->getLocale() . '/form/*') ? 'active' : '' }}">
                                    <span class="nav-icon uil uil-keyhole-circle"></span>
                                    <span class="menu-text">{{ trans('menu.form-menu-title') }}</span>
                                </a>
                                <ul class="subMenu">
                                    <li><a class="{{ Request::is(app()->getLocale() . '/form/basic') ? 'active' : '' }}"
                                            href="{{ route('form.basic', app()->getLocale()) }}">{{ trans('menu.form-basic') }}</a>
                                    </li>
                                    <li><a class="{{ Request::is(app()->getLocale() . '/form/layout') ? 'active' : '' }}"
                                            href="{{ route('form.layout', app()->getLocale()) }}">{{ trans('menu.form-layout') }}</a>
                                    </li>
                                    <li><a class="{{ Request::is(app()->getLocale() . '/form/element') ? 'active' : '' }}"
                                            href="{{ route('form.element', app()->getLocale()) }}">{{ trans('menu.form-element') }}</a>
                                    </li>
                                    <li><a class="{{ Request::is(app()->getLocale() . '/form/component') ? 'active' : '' }}"
                                            href="{{ route('form.component', app()->getLocale()) }}">{{ trans('menu.form-component') }}</a>
                                    </li>
                                    <li><a class="{{ Request::is(app()->getLocale() . '/form/validation') ? 'active' : '' }}"
                                            href="{{ route('form.validation', app()->getLocale()) }}">{{ trans('menu.form-validation') }}</a>
                                    </li>
                                </ul>
                            </li>
                            <li class="has-subMenu-left">
                                <a href="#" class="{{ Request::is(app()->getLocale() . '/map/*') ? 'active' : '' }}">
                                    <span class="nav-icon uil uil-map"></span>
                                    <span class="menu-text">{{ trans('menu.map-menu-title') }}</span>
                                </a>
                                <ul class="subMenu">
                                    <li><a href="{{ route('map.google', app()->getLocale()) }}"
                                            class="{{ Request::is(app()->getLocale() . '/map/google') ? 'active' : '' }}">{{ trans('menu.map-google') }}</a>
                                    </li>
                                    <li><a href="{{ route('map.leaflet', app()->getLocale()) }}"
                                            class="{{ Request::is(app()->getLocale() . '/map/leaflet') ? 'active' : '' }}">{{ trans('menu.map-leaflet') }}</a>
                                    </li>
                                    <li><a href="{{ route('map.vector', app()->getLocale()) }}"
                                            class="{{ Request::is(app()->getLocale() . '/map/vector') ? 'active' : '' }}">{{ trans('menu.map-vector') }}</a>
                                    </li>
                                </ul>
                            </li>
                            <li class="has-subMenu-left">
                                <a href="#" class="{{ Request::is(app()->getLocale() . '/widget/*') ? 'active' : '' }}">
                                    <span class="nav-icon uil uil-server"></span>
                                    <span class="menu-text">{{ trans('menu.widget-menu-title') }}</span>
                                </a>
                                <ul class="subMenu">
                                    <li><a class="{{ Request::is(app()->getLocale() . '/widget/chart') ? 'active' : '' }}"
                                            href="{{ route('widget.chart', app()->getLocale()) }}">{{ trans('menu.widget-chart') }}</a>
                                    </li>
                                    <li><a class="{{ Request::is(app()->getLocale() . '/widget/mixed') ? 'active' : '' }}"
                                            href="{{ route('widget.mixed', app()->getLocale()) }}">{{ trans('menu.widget-mixed') }}</a>
                                    </li>
                                    <li><a class="{{ Request::is(app()->getLocale() . '/widget/card') ? 'active' : '' }}"
                                            href="{{ route('widget.card', app()->getLocale()) }}">{{ trans('menu.widget-card') }}</a>
                                    </li>
                                </ul>
                            </li>
                            <li class="has-subMenu-left">
                                <a href="#" class="{{ Request::is(app()->getLocale() . '/wizard/*') ? 'active' : '' }}">
                                    <span class="nav-icon uil uil-square"></span>
                                    <span class="menu-text">{{ trans('menu.wizard-menu-title') }}</span>
                                </a>
                                <ul class="subMenu">
                                    <li><a href="{{ route('wizard.one', app()->getLocale()) }}"
                                            class="{{ Request::is(app()->getLocale() . '/wizard/one') ? 'active' : '' }}">{{ trans('menu.wizard-one') }}</a>
                                    </li>
                                    <li><a href="{{ route('wizard.two', app()->getLocale()) }}"
                                            class="{{ Request::is(app()->getLocale() . '/wizard/two') ? 'active' : '' }}">{{ trans('menu.wizard-two') }}</a>
                                    </li>
                                    <li><a href="{{ route('wizard.three', app()->getLocale()) }}"
                                            class="{{ Request::is(app()->getLocale() . '/wizard/three') ? 'active' : '' }}">{{ trans('menu.wizard-three') }}</a>
                                    </li>
                                    <li><a href="{{ route('wizard.four', app()->getLocale()) }}"
                                            class="{{ Request::is(app()->getLocale() . '/wizard/four') ? 'active' : '' }}">{{ trans('menu.wizard-four') }}</a>
                                    </li>
                                    <li><a href="{{ route('wizard.five', app()->getLocale()) }}"
                                            class="{{ Request::is(app()->getLocale() . '/wizard/five') ? 'active' : '' }}">{{ trans('menu.wizard-five') }}</a>
                                    </li>
                                </ul>
                            </li>
                        </ul>
                    </li>
                    <li class="mega-item has-subMenu">
                        <a href="#"
                            class="{{ Request::is(app()->getLocale() . '/pages/pricing') || Request::is(app()->getLocale() . '/pages/gallery/*') || Request::is(app()->getLocale() . '/changelog') ? 'active' : '' }}">Pages</a>
                        <ul class="megaMenu-wrapper megaMenu-small">
                            <li>
                                <ul>
                                    <li>
                                        <a href="{{ route('changelog', app()->getLocale()) }}"
                                            class="{{ Request::is(app()->getLocale() . '/changelog') ? 'active' : '' }}">
                                            <span class="nav-icon uil uil-arrow-growth"></span>
                                            <span class="menu-text">{{ trans('menu.changelog-menu-title') }}</span>
                                            <span class="badge badge-info-10 menuItem rounded-pill">1.0.1</span>
                                        </a>
                                    </li>
                                    <li><a href="{{ route('pages.gallery1', app()->getLocale()) }}"
                                            class="{{ Request::is(app()->getLocale() . '/pages/gallery/gallery1') ? 'active' : '' }}">{{ trans('menu.gallery-one') }}</a>
                                    </li>
                                    <li>
                                        <a href="{{ route('pages.gallery2', app()->getLocale()) }}"
                                            class="{{ Request::is(app()->getLocale() . '/pages/gallery/gallery2') ? 'active' : '' }}">{{ trans('menu.gallery-two') }}</a>
                                    </li>
                                    <li>
                                        <a href="{{ route('pages.pricing', app()->getLocale()) }}"
                                            class="{{ Request::is(app()->getLocale() . '/pages/pricing') ? 'active' : '' }}">
                                            <span class="nav-icon uil uil-bill"></span>
                                            <span class="menu-text">{{ trans('menu.pricing-menu-title') }}</span>
                                        </a>
                                    </li>
                                    <li>
                                        <a href="{{ route('pages.banner', app()->getLocale()) }}"
                                            class="{{ Request::is(app()->getLocale() . '/pages/banner') ? 'active' : '' }}">
                                            <span class="nav-icon uil uil-thumbs-up"></span>
                                            <span class="menu-text">{{ trans('menu.banner-menu-title') }}</span>
                                        </a>
                                    </li>
                                    <li>
                                        <a href="{{ route('pages.testimonial', app()->getLocale()) }}"
                                            class="{{ Request::is(app()->getLocale() . '/pages/testimonial') ? 'active' : '' }}">
                                            <span class="nav-icon uil uil-book-open"></span>
                                            <span class="menu-text">{{ trans('menu.testimonial-menu-title') }}</span>
                                        </a>
                                    </li>
                                    <li>
                                        <a href="{{ route('pages.faq', app()->getLocale()) }}"
                                            class="{{ Request::is(app()->getLocale() . '/pages/faq') ? 'active' : '' }}">
                                            <span class="nav-icon uil uil-question-circle"></span>
                                            <span class="menu-text">{{ trans('menu.faq-menu-title') }}</span>
                                        </a>
                                    </li>

                                    <li>
                                        <a href="{{ route('pages.search_result', app()->getLocale()) }}"
                                            class="{{ Request::is(app()->getLocale() . '/pages/search/result') ? 'active' : '' }}">
                                            <span class="nav-icon uil uil-credit-card-search"></span>
                                            <span class="menu-text">{{ trans('menu.search-menu-title') }}</span>
                                        </a>
                                    </li>
                                    <li>
                                        <a href="{{ route('pages.blank', app()->getLocale()) }}"
                                            class="{{ Request::is(app()->getLocale() . '/pages/blank') ? 'active' : '' }}">
                                            <span class="nav-icon uil uil-circle"></span>
                                            <span class="menu-text">{{ trans('menu.blank-menu-title') }}</span>
                                        </a>
                                    </li>
                                    <li><a class="{{ Request::is(app()->getLocale() . '/pages/knowledge/base') ? 'active' : '' }}"
                                            href="{{ route('pages.knowledge_base', app()->getLocale()) }}">{{ trans('menu.knowledge-base') }}</a>
                                    </li>
                                    <li><a class="{{ Request::is(app()->getLocale() . '/pages/knowledge/all-articles') ? 'active' : '' }}"
                                            href="{{ route('pages.all_articles', app()->getLocale()) }}">{{ trans('menu.knowledge-all-article') }}</a>
                                    </li>
                                    <li><a class="{{ Request::is(app()->getLocale() . '/pages/knowledge/article') ? 'active' : '' }}"
                                            href="{{ route('pages.article', app()->getLocale()) }}">{{ trans('menu.knowledge-single-article') }}</a>
                                    </li>
                                </ul>
                            </li>
                            <li>
                                <ul>
                                    <li>
                                        <a href="{{ route('pages.support', app()->getLocale()) }}"
                                            class="{{ Request::is(app()->getLocale() . '/pages/support/center') ? 'active' : '' }}">
                                            <span class="nav-icon uil uil-headphones"></span>
                                            <span class="menu-text">{{ trans('menu.support-menu-title') }}</span>
                                        </a>
                                    </li>
                                    <li><a href="{{ route('pages.blog.one', app()->getLocale()) }}"
                                            class="{{ Request::is(app()->getLocale() . '/pages/blog/one') ? 'active' : '' }}">{{ trans('menu.blog-style-one') }}</a>
                                    </li>
                                    <li><a href="{{ route('pages.blog.two', app()->getLocale()) }}"
                                            class="{{ Request::is(app()->getLocale() . '/pages/blog/two') ? 'active' : '' }}">{{ trans('menu.blog-style-two') }}</a>
                                    </li>
                                    <li><a href="{{ route('pages.blog.three', app()->getLocale()) }}"
                                            class="{{ Request::is(app()->getLocale() . '/pages/blog/three') ? 'active' : '' }}">{{ trans('menu.blog-style-three') }}</a>
                                    </li>
                                    <li><a href="{{ route('pages.blog.detail', app()->getLocale()) }}"
                                            class="{{ Request::is(app()->getLocale() . '/pages/blog/detail') ? 'active' : '' }}">{{ trans('menu.blog-detail') }}</a>
                                    </li>
                                    <li>
                                        <a href="{{ route('pages.terms', app()->getLocale()) }}"
                                            class="{{ Request::is(app()->getLocale() . '/pages/terms-and-condition') ? 'active' : '' }}">
                                            <span class="nav-icon uil uil-question-circle"></span>
                                            <span class="menu-text">{{ trans('menu.terms-menu-title') }}</span>
                                        </a>
                                    </li>
                                    <li>
                                        <a href="{{ route('pages.maintenance', app()->getLocale()) }}"
                                            class="{{ Request::is(app()->getLocale() . '/pages/maintenance') ? 'active' : '' }}">
                                            <span class="nav-icon uil uil-airplay"></span>
                                            <span class="menu-text">{{ trans('menu.maintenance-menu-title') }}</span>
                                        </a>
                                    </li>
                                    <li>
                                        <a href="{{ route('pages.404', app()->getLocale()) }}"
                                            class="{{ Request::is(app()->getLocale() . '/pages/404') ? 'active' : '' }}">
                                            <span class="nav-icon uil uil-exclamation-triangle"></span>
                                            <span class="menu-text">{{ trans('menu.not-found-menu-title') }}</span>
                                        </a>
                                    </li>
                                    <li>
                                        <a href="{{ route('pages.coming_soon', app()->getLocale()) }}"
                                            class="{{ Request::is(app()->getLocale() . '/pages/coming-soon') ? 'active' : '' }}">
                                            <span class="nav-icon uil uil-sync"></span>
                                            <span class="menu-text">{{ trans('menu.coming-soon-menu-title') }}</span>
                                        </a>
                                    </li>
                                </ul>
                            </li>
                        </ul>
                    </li>
                    <li class="mega-item has-subMenu">
                        <a href="#"
                            class="{{ Request::is(app()->getLocale() . '/ui/*') ? 'active' : '' }}">Components</a>
                        <ul class="megaMenu-wrapper megaMenu-wide">
                            <li>
                                <span class="mega-title">Components</span>
                                <ul>
                                    <li><a class="{{ Request::is(app()->getLocale() . '/ui/alert') ? 'active' : '' }}"
                                            href="{{ route('ui.alert', app()->getLocale()) }}">{{ trans('menu.ui-alert') }}</a>
                                    </li>
                                    <li><a class="{{ Request::is(app()->getLocale() . '/ui/avatar') ? 'active' : '' }}"
                                            href="{{ route('ui.avatar', app()->getLocale()) }}">{{ trans('menu.ui-avatar') }}</a>
                                    </li>
                                    <li><a class="{{ Request::is(app()->getLocale() . '/ui/badge') ? 'active' : '' }}"
                                            href="{{ route('ui.badge', app()->getLocale()) }}">{{ trans('menu.ui-badge') }}</a>
                                    </li>
                                    <li><a class="{{ Request::is(app()->getLocale() . '/ui/breadcrumps') ? 'active' : '' }}"
                                            href="{{ route('ui.breadcrumps', app()->getLocale()) }}">{{ trans('menu.ui-breadcrumb') }}</a>
                                    </li>
                                    <li><a class="{{ Request::is(app()->getLocale() . '/ui/button') ? 'active' : '' }}"
                                            href="{{ route('ui.button', app()->getLocale()) }}">{{ trans('menu.ui-button') }}</a>
                                    </li>
                                    <li><a class="{{ Request::is(app()->getLocale() . '/ui/card') ? 'active' : '' }}"
                                            href="{{ route('ui.card', app()->getLocale()) }}">{{ trans('menu.ui-card') }}</a>
                                    </li>
                                    <li><a class="{{ Request::is(app()->getLocale() . '/ui/carousel') ? 'active' : '' }}"
                                            href="{{ route('ui.carousel', app()->getLocale()) }}">{{ trans('menu.ui-carousel') }}</a>
                                    </li>
                                    <li><a class="{{ Request::is(app()->getLocale() . '/ui/checkbox') ? 'active' : '' }}"
                                            href="{{ route('ui.checkbox', app()->getLocale()) }}">{{ trans('menu.ui-checkbox') }}</a>
                                    </li>
                                    <li><a class="{{ Request::is(app()->getLocale() . '/ui/collapse') ? 'active' : '' }}"
                                            href="{{ route('ui.collapse', app()->getLocale()) }}">{{ trans('menu.ui-collapse') }}</a>
                                    </li>
                                    <li><a class="{{ Request::is(app()->getLocale() . '/ui/comments') ? 'active' : '' }}"
                                            href="{{ route('ui.comments', app()->getLocale()) }}">{{ trans('menu.ui-comment') }}</a>
                                    </li>

                                </ul>
                            </li>
                            <li>
                                <span class="mega-title">Components</span>
                                <ul>
                                    <li><a class="{{ Request::is(app()->getLocale() . '/ui/dashboard-base') ? 'active' : '' }}"
                                            href="{{ route('ui.dashboard_base', app()->getLocale()) }}">{{ trans('menu.ui-dashboard-base') }}</a>
                                    </li>
                                    <li><a class="{{ Request::is(app()->getLocale() . '/ui/datepicker') ? 'active' : '' }}"
                                            href="{{ route('ui.datepicker', app()->getLocale()) }}">{{ trans('menu.ui-date-picker') }}</a>
                                    </li>
                                    <li><a class="{{ Request::is(app()->getLocale() . '/ui/drawer') ? 'active' : '' }}"
                                            href="{{ route('ui.drawer', app()->getLocale()) }}">{{ trans('menu.ui-drawer') }}</a>
                                    </li>
                                    <li><a class="{{ Request::is(app()->getLocale() . '/ui/drag-drop') ? 'active' : '' }}"
                                            href="{{ route('ui.drag_drop', app()->getLocale()) }}">{{ trans('menu.ui-drag-drop') }}</a>
                                    </li>
                                    <li><a class="{{ Request::is(app()->getLocale() . '/ui/dropdown') ? 'active' : '' }}"
                                            href="{{ route('ui.dropdown', app()->getLocale()) }}">{{ trans('menu.ui-dropdown') }}</a>
                                    </li>
                                    <li><a class="{{ Request::is(app()->getLocale() . '/ui/empty') ? 'active' : '' }}"
                                            href="{{ route('ui.empty', app()->getLocale()) }}">{{ trans('menu.ui-empty') }}</a>
                                    </li>
                                    <li><a class="{{ Request::is(app()->getLocale() . '/ui/grid') ? 'active' : '' }}"
                                            href="{{ route('ui.grid', app()->getLocale()) }}">{{ trans('menu.ui-grid') }}</a>
                                    </li>
                                    <li><a class="{{ Request::is(app()->getLocale() . '/ui/input') ? 'active' : '' }}"
                                            href="{{ route('ui.input', app()->getLocale()) }}">{{ trans('menu.ui-input') }}</a>
                                    </li>
                                    <li><a class="{{ Request::is(app()->getLocale() . '/ui/list') ? 'active' : '' }}"
                                            href="{{ route('ui.list', app()->getLocale()) }}">{{ trans('menu.ui-list') }}</a>
                                    </li>
                                    <li><a class="{{ Request::is(app()->getLocale() . '/ui/menu') ? 'active' : '' }}"
                                            href="{{ route('ui.menu', app()->getLocale()) }}">{{ trans('menu.ui-menu') }}</a>
                                    </li>
                                    <li><a class="{{ Request::is(app()->getLocale() . '/ui/message') ? 'active' : '' }}"
                                            href="{{ route('ui.message', app()->getLocale()) }}">{{ trans('menu.ui-message') }}</a>
                                    </li>

                                </ul>
                            </li>
                            <li>
                                <span class="mega-title">Components</span>
                                <ul>
                                    <li><a class="{{ Request::is(app()->getLocale() . '/ui/modals') ? 'active' : '' }}"
                                            href="{{ route('ui.modals', app()->getLocale()) }}">{{ trans('menu.ui-modal') }}</a>
                                    </li>
                                    <li><a class="{{ Request::is(app()->getLocale() . '/ui/notification') ? 'active' : '' }}"
                                            href="{{ route('ui.notification', app()->getLocale()) }}">{{ trans('menu.ui-notification') }}</a>
                                    </li>
                                    <li><a class="{{ Request::is(app()->getLocale() . '/ui/page-header') ? 'active' : '' }}"
                                            href="{{ route('ui.page_header', app()->getLocale()) }}">{{ trans('menu.ui-page-header') }}</a>
                                    </li>
                                    <li><a class="{{ Request::is(app()->getLocale() . '/ui/pagination') ? 'active' : '' }}"
                                            href="{{ route('ui.pagination', app()->getLocale()) }}">{{ trans('menu.ui-pagination') }}</a>
                                    </li>
                                    <li><a class="{{ Request::is(app()->getLocale() . '/ui/progress') ? 'active' : '' }}"
                                            href="{{ route('ui.progress', app()->getLocale()) }}">{{ trans('menu.ui-progress') }}</a>
                                    </li>
                                    <li><a class="{{ Request::is(app()->getLocale() . '/ui/radio') ? 'active' : '' }}"
                                            href="{{ route('ui.radio', app()->getLocale()) }}">{{ trans('menu.ui-radio') }}</a>
                                    </li>
                                    <li><a class="{{ Request::is(app()->getLocale() . '/ui/rate') ? 'active' : '' }}"
                                            href="{{ route('ui.rate', app()->getLocale()) }}">{{ trans('menu.ui-rate') }}</a>
                                    </li>
                                    <li><a class="{{ Request::is(app()->getLocale() . '/ui/result') ? 'active' : '' }}"
                                            href="{{ route('ui.result', app()->getLocale()) }}">{{ trans('menu.ui-result') }}</a>
                                    </li>
                                    <li><a class="{{ Request::is(app()->getLocale() . '/ui/select') ? 'active' : '' }}"
                                            href="{{ route('ui.select', app()->getLocale()) }}">{{ trans('menu.ui-select') }}</a>
                                    </li>
                                    <li><a class="{{ Request::is(app()->getLocale() . '/ui/skeleton') ? 'active' : '' }}"
                                            href="{{ route('ui.skeleton', app()->getLocale()) }}">{{ trans('menu.ui-skeleton') }}</a>
                                    </li>

                                    <li><a class="{{ Request::is(app()->getLocale() . '/ui/timepicker') ? 'active' : '' }}"
                                            href="{{ route('ui.timepicker', app()->getLocale()) }}">{{ trans('menu.ui-time-picker') }}</a>
                                    </li>

                                </ul>
                            </li>
                            <li>
                                <span class="mega-title">Components</span>
                                <ul>
                                    <li><a class="{{ Request::is(app()->getLocale() . '/ui/slider') ? 'active' : '' }}"
                                            href="{{ route('ui.slider', app()->getLocale()) }}">{{ trans('menu.ui-slider') }}</a>
                                    </li>
                                    <li><a class="{{ Request::is(app()->getLocale() . '/ui/spinner') ? 'active' : '' }}"
                                            href="{{ route('ui.spinner', app()->getLocale()) }}">{{ trans('menu.ui-spinner') }}</a>
                                    </li>
                                    <li><a class="{{ Request::is(app()->getLocale() . '/ui/statistic') ? 'active' : '' }}"
                                            href="{{ route('ui.statistic', app()->getLocale()) }}">{{ trans('menu.ui-statistic') }}</a>
                                    </li>
                                    <li><a class="{{ Request::is(app()->getLocale() . '/ui/steps') ? 'active' : '' }}"
                                            href="{{ route('ui.steps', app()->getLocale()) }}">{{ trans('menu.ui-step') }}</a>
                                    </li>
                                    <li><a class="{{ Request::is(app()->getLocale() . '/ui/switch') ? 'active' : '' }}"
                                            href="{{ route('ui.switch', app()->getLocale()) }}">{{ trans('menu.ui-switch') }}</a>
                                    </li>
                                    <li><a class="{{ Request::is(app()->getLocale() . '/ui/tab') ? 'active' : '' }}"
                                            href="{{ route('ui.tab', app()->getLocale()) }}">{{ trans('menu.ui-tab') }}</a>
                                    </li>
                                    <li><a class="{{ Request::is(app()->getLocale() . '/ui/tags') ? 'active' : '' }}"
                                            href="{{ route('ui.tags', app()->getLocale()) }}">{{ trans('menu.ui-tag') }}</a>
                                    </li>
                                    <li><a class="{{ Request::is(app()->getLocale() . '/ui/timeline') ? 'active' : '' }}"
                                            href="{{ route('ui.timeline', app()->getLocale()) }}">{{ trans('menu.ui-timeline') }}</a>
                                    </li>
                                    <li><a class="{{ Request::is(app()->getLocale() . '/ui/timeline2') ? 'active' : '' }}"
                                            href="{{ route('ui.timeline2', app()->getLocale()) }}">{{ trans('menu.ui-timeline2') }}</a>
                                    </li>
                                    <li><a class="{{ Request::is(app()->getLocale() . '/ui/timeline3') ? 'active' : '' }}"
                                            href="{{ route('ui.timeline3', app()->getLocale()) }}">{{ trans('menu.ui-timeline3') }}</a>
                                    </li>
                                    <li><a class="{{ Request::is(app()->getLocale() . '/ui/uploads') ? 'active' : '' }}"
                                            href="{{ route('ui.uploads', app()->getLocale()) }}">{{ trans('menu.ui-upload') }}</a>
                                    </li>
                                </ul>
                            </li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </div>
    <div class="navbar-right">
        <ul class="navbar-right__menu">
            <li class="nav-search">
                <a href="#" class="search-toggle">
                    <i class="uil uil-search"></i>
                    <i class="uil uil-times"></i>
                </a>
                <form action="/" class="search-form-topMenu">
                    <span class="search-icon uil uil-search"></span>
                    <input class="form-control me-sm-2 box-shadow-none" type="search" placeholder="Search..."
                        aria-label="Search">
                </form>
            </li>
            <li class="nav-message">
                @php
                    $waitingQueue = \App\Models\Appointment::whereDate('appointment_date', today())
                        ->whereIn('status', ['waiting', 'screening', 'ready'])
                        ->with('patient')
                        ->orderBy('queue_number')
                        ->take(5)
                        ->get();
                    $waitingCount = \App\Models\Appointment::whereDate('appointment_date', today())
                        ->whereIn('status', ['waiting', 'screening', 'ready'])
                        ->count();
                @endphp
                <div class="dropdown-custom">
                    <a href="javascript:;" class="nav-item-toggle {{ $waitingCount > 0 ? 'icon-active' : '' }}">
                        <img class="svg" src="{{ asset('assets/img/svg/message.svg') }}" alt="img">
                    </a>
                    <div class="dropdown-wrapper">
                        <h2 class="dropdown-wrapper__title">Antrian Hari Ini @if($waitingCount > 0)<span
                        class="badge-circle badge-warning ms-1">{{ $waitingCount }}</span>@endif</h2>
                        <ul>
                            @forelse($waitingQueue as $queue)
                                <li class="author-online has-new-message">
                                    <div class="user-avater bg-primary text-white rounded-circle d-flex align-items-center justify-content-center"
                                        style="width: 40px; height: 40px; font-weight: bold;">
                                        {{ $queue->queue_number }}
                                    </div>
                                    <div class="user-message">
                                        <p>
                                            <a href="{{ route('clinic.appointments.show', [app()->getLocale(), $queue->id]) }}"
                                                class="subject stretched-link text-truncate"
                                                style="max-width: 180px;">{{ $queue->patient->name ?? 'Pasien' }}</a>
                                            <span class="time-posted">No. RM: {{ $queue->patient->no_rm ?? '-' }}</span>
                                        </p>
                                        <p>
                                            <span class="desc text-truncate"
                                                style="max-width: 215px;">{{ $queue->statusLabel() }}{{ $queue->complaint ? ' - ' . Str::limit($queue->complaint, 30) : '' }}</span>
                                        </p>
                                    </div>
                                </li>
                            @empty
                                <li class="text-center py-3 text-muted">
                                    <i class="uil uil-check-circle fs-4"></i>
                                    <p class="mb-0">Tidak ada antrian menunggu</p>
                                </li>
                            @endforelse
                        </ul>
                        <a href="{{ route('clinic.appointments.today', app()->getLocale()) }}"
                            class="dropdown-wrapper__more">Lihat Semua Antrian</a>
                    </div>
                </div>
            </li>
            <li class="nav-notification">
                @php
                    // Notifications follow the user's permissions
                    $canFinance = auth()->user()?->can('reports.finance');
                    $canPayments = auth()->user()?->can('payments.view');
                    $canMedicines = auth()->user()?->can('medicines.view');

                    $pendingPayments = $canPayments ? \App\Models\Payment::where('status', 'pending')->count() : 0;
                    $lowStockMedicines = $canMedicines
                        ? \App\Models\Medicine::where('status', 'active')->whereColumn('stock', '<=', 'min_stock')->take(5)->get()
                        : collect();
                    $lowStockCount = $canMedicines
                        ? \App\Models\Medicine::where('status', 'active')->whereColumn('stock', '<=', 'min_stock')->count()
                        : 0;
                    $todayPayments = $canFinance
                        ? \App\Models\Payment::revenueBetween(today()->startOfDay(), today()->endOfDay())['net']
                        : 0;
                    $notifCount = $pendingPayments + $lowStockCount;
                @endphp
                <div class="dropdown-custom">
                    <a href="javascript:;" class="nav-item-toggle {{ $notifCount > 0 ? 'icon-active' : '' }}">
                        <img class="svg" src="{{ asset('assets/img/svg/alarm.svg') }}" alt="img">
                    </a>
                    <div class="dropdown-wrapper">
                        <h2 class="dropdown-wrapper__title">Notifikasi @if($notifCount > 0)<span
                        class="badge-circle badge-warning ms-1">{{ $notifCount }}</span>@endif</h2>
                        <ul>
                            {{-- Pendapatan Hari Ini --}}
                            @if($canFinance)
                            <li class="nav-notification__single d-flex flex-wrap">
                                <div class="nav-notification__type nav-notification__type--success">
                                    <i class="uil uil-money-bill fs-5"></i>
                                </div>
                                <div class="nav-notification__details">
                                    <p>
                                        <a href="{{ route('clinic.reports.daily', app()->getLocale()) }}"
                                            class="subject stretched-link">Pendapatan Hari Ini</a>
                                    </p>
                                    <p>
                                        <span class="time-posted fw-bold text-success">Rp
                                            {{ number_format($todayPayments, 0, ',', '.') }}</span>
                                    </p>
                                </div>
                            </li>
                            @endif

                            {{-- Pembayaran Pending --}}
                            @if($pendingPayments > 0)
                                <li class="nav-notification__single nav-notification__single--unread d-flex flex-wrap">
                                    <div class="nav-notification__type nav-notification__type--warning">
                                        <i class="uil uil-bill fs-5"></i>
                                    </div>
                                    <div class="nav-notification__details">
                                        <p>
                                            <a href="{{ route('clinic.payments.index', app()->getLocale()) }}"
                                                class="subject stretched-link">Pembayaran Pending</a>
                                        </p>
                                        <p>
                                            <span class="time-posted">{{ $pendingPayments }} pembayaran menunggu</span>
                                        </p>
                                    </div>
                                </li>
                            @endif

                            {{-- Obat Stok Rendah --}}
                            @forelse($lowStockMedicines as $medicine)
                                <li class="nav-notification__single nav-notification__single--unread d-flex flex-wrap">
                                    <div class="nav-notification__type nav-notification__type--danger">
                                        <i class="uil uil-capsule fs-5"></i>
                                    </div>
                                    <div class="nav-notification__details">
                                        <p>
                                            <a href="{{ route('clinic.medicines.low-stock', app()->getLocale()) }}"
                                                class="subject stretched-link text-truncate"
                                                style="max-width: 180px;">{{ $medicine->name }}</a>
                                            <span class="text-danger">Stok Rendah!</span>
                                        </p>
                                        <p>
                                            <span class="time-posted">Tersisa: {{ $medicine->stock }}
                                                {{ $medicine->unit }}</span>
                                        </p>
                                    </div>
                                </li>
                            @empty
                                @if($pendingPayments == 0)
                                    <li class="text-center py-3 text-muted">
                                        <i class="uil uil-check-circle fs-4 text-success"></i>
                                        <p class="mb-0">Semua aman! Tidak ada notifikasi</p>
                                    </li>
                                @endif
                            @endforelse
                        </ul>
                        <a href="{{ route('clinic.dashboard', app()->getLocale()) }}"
                            class="dropdown-wrapper__more">Lihat Dashboard</a>
                    </div>
                </div>
            </li>
            <li class="nav-mode-switch">
                <a href="#" class="nav-item-toggle" id="darkModeToggle" title="Mode Gelap/Terang">
                    <i class="uil uil-sun" id="modeIcon" style="font-size: 22px; color: #5a5f7d;"></i>
                </a>
                <script>
                    (function () {
                        // Check saved preference
                        var savedMode = localStorage.getItem('themeMode');
                        var body = document.body;
                        var icon = document.getElementById('modeIcon');

                        if (savedMode === 'dark') {
                            body.classList.remove('layout-light');
                            body.classList.add('layout-dark');
                            if (icon) {
                                icon.classList.remove('uil-sun');
                                icon.classList.add('uil-moon');
                            }
                        } else {
                            body.classList.remove('layout-dark');
                            body.classList.add('layout-light');
                            if (icon) {
                                icon.classList.remove('uil-moon');
                                icon.classList.add('uil-sun');
                            }
                        }

                        // Add click handler
                        document.addEventListener('DOMContentLoaded', function () {
                            var toggle = document.getElementById('darkModeToggle');
                            var modeIcon = document.getElementById('modeIcon');

                            if (toggle) {
                                toggle.addEventListener('click', function (e) {
                                    e.preventDefault();

                                    if (document.body.classList.contains('layout-dark')) {
                                        // Switch to light
                                        document.body.classList.remove('layout-dark');
                                        document.body.classList.add('layout-light');
                                        modeIcon.classList.remove('uil-moon');
                                        modeIcon.classList.add('uil-sun');
                                        localStorage.setItem('themeMode', 'light');
                                    } else {
                                        // Switch to dark
                                        document.body.classList.remove('layout-light');
                                        document.body.classList.add('layout-dark');
                                        modeIcon.classList.remove('uil-sun');
                                        modeIcon.classList.add('uil-moon');
                                        localStorage.setItem('themeMode', 'dark');
                                    }
                                });
                            }
                        });
                    })();
                </script>
            </li>
            <li class="nav-author">
                <div class="dropdown-custom">
                    <a href="javascript:;" class="nav-item-toggle">
                        @if(Auth::check() && Auth::user()->avatar)
                            <img src="{{ Auth::user()->avatar }}" alt="{{ Auth::user()->name }}"
                                class="avatar avatar-circle avatar-sm"
                                style="width: 32px; height: 32px; object-fit: cover;">
                        @else
                            <div class="avatar avatar-circle avatar-sm bg-primary text-white d-flex align-items-center justify-content-center"
                                style="width: 32px; height: 32px;">
                                <span
                                    style="font-size: 14px; font-weight: 600;">{{ Auth::check() ? strtoupper(substr(Auth::user()->name, 0, 1)) : 'U' }}</span>
                            </div>
                        @endif
                        @if(Auth::check())
                            <span class="nav-item__title">{{ Auth::user()->name }}<i
                                    class="las la-angle-down nav-item__arrow"></i></span>
                        @endif
                    </a>
                    <div class="dropdown-wrapper">
                        <div class="nav-author__info">
                            <div class="author-img">
                                @if(Auth::check() && Auth::user()->avatar)
                                    <img src="{{ Auth::user()->avatar }}" alt="{{ Auth::user()->name }}"
                                        class="avatar avatar-circle" style="width: 50px; height: 50px; object-fit: cover;">
                                @else
                                    <div class="avatar avatar-circle bg-primary text-white d-flex align-items-center justify-content-center"
                                        style="width: 50px; height: 50px;">
                                        <span
                                            style="font-size: 20px; font-weight: 600;">{{ Auth::check() ? strtoupper(substr(Auth::user()->name, 0, 1)) : 'U' }}</span>
                                    </div>
                                @endif
                            </div>
                            <div>
                                @if(Auth::check())
                                    <h6 class="text-capitalize">{{ Auth::user()->name }}</h6>
                                    <span class="text-primary">{{ Auth::user()->roleLabel() }}</span>
                                @endif
                            </div>
                        </div>
                        <div class="nav-author__options">
                            <ul>
                                <li>
                                    <a href="{{ route('clinic.dashboard', app()->getLocale()) }}">
                                        <i class="uil uil-home-alt me-2"></i>Dashboard</a>
                                </li>
                                @can('users.manage')
                                    <li>
                                        <a href="{{ route('clinic.users.edit', [app()->getLocale(), Auth::id()]) }}">
                                            <i class="uil uil-user me-2"></i>Profil Saya</a>
                                    </li>
                                @endcan
                                @can('settings.manage')
                                    <li>
                                        <a href="{{ route('clinic.settings.profile', app()->getLocale()) }}">
                                            <i class="uil uil-building me-2"></i>Profil Klinik</a>
                                    </li>
                                @endcan
                                @can('users.manage')
                                    <li>
                                        <a href="{{ route('clinic.users.index', app()->getLocale()) }}">
                                            <i class="uil uil-users-alt me-2"></i>Manajemen User</a>
                                    </li>
                                @endcan
                            </ul>
                            <a href="" class="nav-author__signout"
                                onclick="event.preventDefault();document.getElementById('logout').submit();">
                                <i class="uil uil-sign-out-alt me-2"></i>Keluar</a>
                            <form style="display:none;" id="logout" action="{{ route('logout') }}" method="POST">
                                @csrf
                                @method('post')
                            </form>
                        </div>
                    </div>
                </div>
            </li>
        </ul>
        <div class="navbar-right__mobileAction d-md-none">
            <a href="#" class="btn-search">
                <img src="{{ asset('assets/img/svg/search.svg') }}" alt="search" class="svg feather-search">
                <img src="{{ asset('assets/img/svg/x.svg') }}" alt="x" class="svg feather-x">
            </a>
            <a href="#" class="btn-author-action">
                <img src="{{ asset('assets/img/svg/more-vertical.svg') }}" alt="more-vertical" class="svg"></a>
        </div>
    </div>
</nav>