<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <meta name="description" content="" />
    <meta name="author" content="" />
    <title>MAH - Admin Portal</title>
    <link href="css/styles.css" rel="stylesheet" />
    <link rel="icon" type="image/x-icon" href="../public/assets/img/mah-favicon.png">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://use.fontawesome.com/releases/v6.3.0/js/all.js" crossorigin="anonymous"></script>
</head>

<body class="sb-nav-fixed">
    <nav class="sb-topnav navbar navbar-expand navbar-dark bg-dark">
        <!-- Navbar Brand-->
        <a class="navbar-brand ps-3" href="index.php">MAH Kitchen Portal</a>
        <!-- Sidebar Toggle-->
        <button class="btn btn-link btn-sm order-1 order-lg-0 me-4 me-lg-0" id="sidebarToggle" href="#!"><i class="fas fa-bars"></i></button>
        <!-- Navbar Search-->
        <form class="d-none d-md-inline-block form-inline ms-auto me-0 me-md-3 my-2 my-md-0">
            <!-- <div class="input-group">
                <input class="form-control" type="text" placeholder="Search for..." aria-label="Search for..." aria-describedby="btnNavbarSearch" />
                <button class="btn btn-primary" id="btnNavbarSearch" type="button"><i class="fas fa-search"></i></button>
            </div> -->
        </form>
        <!-- Navbar-->
        <ul class="navbar-nav ms-auto ms-md-0 me-3 me-lg-4">
            <li class="nav-item dropdown">
                <a class="nav-link dropdown-toggle" id="navbarDropdown" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false"><i class="fas fa-user fa-fw"></i></a>
                <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
                    <!-- <li><a class="dropdown-item" href="#!">Settings</a></li>
                    <li><a class="dropdown-item" href="#!">Activity Log</a></li>
                    <li>
                        <hr class="dropdown-divider" />
                    </li> -->
                    <li><a class="dropdown-item" href="logout.php">Logout</a></li>
                </ul>
            </li>
        </ul>
    </nav>
    <div id="layoutSidenav">
        <div id="layoutSidenav_nav">
            <nav class="sb-sidenav accordion sb-sidenav-dark" id="sidenavAccordion">
                <div class="sb-sidenav-menu">
                    <div class="nav">
                        <!-- <div class="sb-sidenav-menu-heading">Core</div> -->
                        <a class="nav-link" href="index.php">
                            <div class="sb-nav-link-icon"></div>
                            Dashboard
                        </a>
                        <hr class="m-0">
                        <a class="nav-link" href="customer.php">
                            <div class="sb-nav-link-icon"></div>
                            Manage Customer
                        </a>
                        <hr class="m-0">
                        <a class="nav-link" href="orders_breakfast.php">
                            <div class="sb-nav-link-icon"></div>
                            Manage BreakFast Orders
                        </a>
                        <a class="nav-link" href="orders.php">
                            <div class="sb-nav-link-icon"></div>
                            Manage Lunch Orders
                        </a>

                        <a class="nav-link" href="orders_dinner.php">
                            <div class="sb-nav-link-icon"></div>
                            Manage Dinner Orders
                        </a>

                        <hr class="m-0">
                        <a class="nav-link" href="delivery.php">
                            <div class="sb-nav-link-icon"></div>
                            Manage Deliveries
                        </a>
                        <hr class="m-0">
                        <a class="nav-link" href="daily_breakfast_status.php">
                            <div class="sb-nav-link-icon"></div>
                            BreakFast Update Center
                        </a>
                        <a class="nav-link" href="daily-status.php">
                            <div class="sb-nav-link-icon"></div>
                            Lunch Update Center
                        </a>

                        <a class="nav-link" href="daily_dinner_status.php">
                            <div class="sb-nav-link-icon"></div>
                            Dinner Update Center
                        </a>
                        <hr class="m-0">
                        <a class="nav-link" href="rider_ledger.php">
                            <div class="sb-nav-link-icon"></div>
                            Riders Ledger
                        </a>

                        <hr class="m-0">
                        <a class="nav-link" href="follow_up.php">
                            <div class="sb-nav-link-icon"></div>
                            Follow Up Section
                        </a>
                        <hr class="m-0">
                        <!-- <a class="nav-link" href="raw_material.php">
                            <div class="sb-nav-link-icon"></div>
                            Raw Materials
                        </a> -->
                        <a class="nav-link" href="raw_material_ledger.php">
                            <div class="sb-nav-link-icon"></div>
                            Raw Materials Ledger
                        </a>
                        <hr class="m-0">
                        <a class="nav-link" href="breakfast_delivery_schedule.php">
                            <div class="sb-nav-link-icon"></div>
                            Breakfast Delivery Schedule
                        </a>
                        <a class="nav-link" href="delivery_schedule.php">
                            <div class="sb-nav-link-icon"></div>
                            Lunch Delivery Schedule
                        </a>
                        <a class="nav-link" href="dinner_delivery_schedule.php">
                            <div class="sb-nav-link-icon"></div>
                            Dinner Delivery Schedule
                        </a>
                        <hr class="m-0">

                        <!-- <div class="sb-sidenav-menu-heading">Interface</div>
                        <a class="nav-link collapsed" href="#" data-bs-toggle="collapse" data-bs-target="#collapseLayouts" aria-expanded="false" aria-controls="collapseLayouts">
                            <div class="sb-nav-link-icon"><i class="fas fa-columns"></i></div>
                            Layouts
                            <div class="sb-sidenav-collapse-arrow"><i class="fas fa-angle-down"></i></div>
                        </a>
                        <div class="collapse" id="collapseLayouts" aria-labelledby="headingOne" data-bs-parent="#sidenavAccordion">
                            <nav class="sb-sidenav-menu-nested nav">
                                <a class="nav-link" href="layout-static.php">Static Navigation</a>
                                <a class="nav-link" href="layout-sidenav-light.php">Light Sidenav</a>
                            </nav>
                        </div>
                        <a class="nav-link collapsed" href="#" data-bs-toggle="collapse" data-bs-target="#collapsePages" aria-expanded="false" aria-controls="collapsePages">
                            <div class="sb-nav-link-icon"><i class="fas fa-book-open"></i></div>
                            Pages
                            <div class="sb-sidenav-collapse-arrow"><i class="fas fa-angle-down"></i></div>
                        </a>
                        <div class="collapse" id="collapsePages" aria-labelledby="headingTwo" data-bs-parent="#sidenavAccordion">
                            <nav class="sb-sidenav-menu-nested nav accordion" id="sidenavAccordionPages">
                                <a class="nav-link collapsed" href="#" data-bs-toggle="collapse" data-bs-target="#pagesCollapseAuth" aria-expanded="false" aria-controls="pagesCollapseAuth">
                                    Authentication
                                    <div class="sb-sidenav-collapse-arrow"><i class="fas fa-angle-down"></i></div>
                                </a>
                                <div class="collapse" id="pagesCollapseAuth" aria-labelledby="headingOne" data-bs-parent="#sidenavAccordionPages">
                                    <nav class="sb-sidenav-menu-nested nav">
                                        <a class="nav-link" href="login.php">Login</a>
                                        <a class="nav-link" href="register.php">Register</a>
                                        <a class="nav-link" href="password.php">Forgot Password</a>
                                    </nav>
                                </div>
                                <a class="nav-link collapsed" href="#" data-bs-toggle="collapse" data-bs-target="#pagesCollapseError" aria-expanded="false" aria-controls="pagesCollapseError">
                                    Error
                                    <div class="sb-sidenav-collapse-arrow"><i class="fas fa-angle-down"></i></div>
                                </a>
                                <div class="collapse" id="pagesCollapseError" aria-labelledby="headingOne" data-bs-parent="#sidenavAccordionPages">
                                    <nav class="sb-sidenav-menu-nested nav">
                                        <a class="nav-link" href="401.php">401 Page</a>
                                        <a class="nav-link" href="404.php">404 Page</a>
                                        <a class="nav-link" href="500.php">500 Page</a>
                                    </nav>
                                </div>
                            </nav>
                        </div>
                        <div class="sb-sidenav-menu-heading">Addons</div>
                        <a class="nav-link" href="charts.php">
                            <div class="sb-nav-link-icon"><i class="fas fa-chart-area"></i></div>
                            Charts
                        </a>
                        <a class="nav-link" href="tables.php">
                            <div class="sb-nav-link-icon"><i class="fas fa-table"></i></div>
                            Tables
                        </a> -->
                    </div>
                </div>
                <div class="sb-sidenav-footer">
                    <div class="small">Logged in as:</div>
                    MAH ADMIN
                </div>
            </nav>
        </div>
        <div id="layoutSidenav_content">
            <main>