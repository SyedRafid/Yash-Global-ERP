<?php
$admin_id = $_SESSION['admin_id'];
//$login_id = $_SESSION['login_id'];
$ret = "SELECT * FROM  user  WHERE id = '$admin_id'";
$stmt = $mysqli->prepare($ret);
$stmt->execute();
$res = $stmt->get_result();
while ($admin = $res->fetch_object()) {
?>
  <nav class="navbar navbar-vertical fixed-left navbar-expand-md navbar-light bg-white" id="sidenav-main">
    <div class="container-fluid">
      <!-- Toggler -->
      <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#sidenav-collapse-main" aria-controls="sidenav-main" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
      </button>
      <!-- Brand -->
      <a class="navbar-brand pt-0" href="dashboard.php">
        <img src="assets/img/brand/repos.png" class="navbar-brand-img" alt="...">
      </a>
      <!-- User -->
      <ul class="nav align-items-center d-md-none">
        <li class="nav-item dropdown">
          <a class="nav-link nav-link-icon" href="#" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
            <i class="ni ni-bell-55"></i>
          </a>
          <div class="dropdown-menu dropdown-menu-arrow dropdown-menu-right" aria-labelledby="navbar-default_dropdown_1">
          </div>
        </li>
        <li class="nav-item dropdown">
          <a class="nav-link" href="#" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
            <div class="media align-items-center">
              <span class="avatar avatar-sm rounded-circle">
                <img alt="Image placeholder" src="assets/img/theme/user-a-min.png">
              </span>
            </div>
          </a>
          <div class="dropdown-menu dropdown-menu-arrow dropdown-menu-right">
            <div class=" dropdown-header noti-title">
              <h6 class="text-overflow m-0">Welcome!</h6>
            </div>
            <a href="change_profile.php" class="dropdown-item">
              <i class="ni ni-single-02"></i>
              <span>My profile</span>
            </a>
            <div class="dropdown-divider"></div>
            <a href="logout.php" class="dropdown-item">
              <i class="ni ni-user-run"></i>
              <span>Logout</span>
            </a>
          </div>
        </li>
      </ul>
      <!-- Collapse -->
      <div class="collapse navbar-collapse" id="sidenav-collapse-main">
        <!-- Collapse header -->
        <div class="navbar-collapse-header d-md-none">
          <div class="row">
            <div class="col-6 collapse-brand">
              <a href="dashboard.php">
                <img src="assets/img/brand/repos.png">
              </a>
            </div>
            <div class="col-6 collapse-close">
              <button type="button" class="navbar-toggler" data-toggle="collapse" data-target="#sidenav-collapse-main" aria-controls="sidenav-main" aria-expanded="false" aria-label="Toggle sidenav">
                <span></span>
                <span></span>
              </button>
            </div>
          </div>
        </div>
        <!-- Form -->
        <!-- <form class="mt-4 mb-3 d-md-none">
          <div class="input-group input-group-rounded input-group-merge">
            <input type="search" class="form-control form-control-rounded form-control-prepended" placeholder="Search" aria-label="Search">
            <div class="input-group-prepend">
              <div class="input-group-text">
                <span class="fa fa-search"></span>
              </div>
            </div>
          </div>
        </form> -->
        <!-- Navigation -->
        <ul class="navbar-nav">
          <li class="nav-item">
            <a class="nav-link" href="dashboard.php">
              <i class="ni ni-tv-2 text-primary"></i> Dashboard
            </a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="user.php">
              <i class="fas fa-users text-primary"></i> Manage Users
            </a>
          </li>
          <li class="nav-item">
            <a href="javascript:void(0);" class="nav-link" data-toggle="collapse" data-target="#submenu-products" aria-expanded="false" aria-controls="submenu-products">
              <i class="nav-icon fas fa-boxes text-primary"></i>
              <span class="menu-text">Manage Products</span>
            </a>
            <div class="collapse" id="submenu-products">
              <ul class="nav nav-sm flex-column">
                <li class="nav-item">
                  <a class="nav-link" href="suppliers.php">
                    <i class="nav-icon fas fa-people-carry text-primary"></i> Suppliers
                  </a>
                </li>
                <li class="nav-item">
                  <a class="nav-link" href="categories.php">
                    <i class="fas fa-tags nav-icon text-primary"></i> Categories
                  </a>
                </li>
                <li class="nav-item">
                  <a class="nav-link" href="products.php">
                    <i class="nav-icon fas fa-box text-primary"></i> Products
                  </a>
                </li>
              </ul>
            </div>
          </li>
          <li class="nav-item">
            <a href="javascript:void(0);" class="nav-link" data-toggle="collapse" data-target="#submenu-inventory" aria-expanded="false" aria-controls="submenu-products">
              <i class="nav-icon fas fa-warehouse text-primary"></i>
              <span class="menu-text">Manage Inventory</span>
            </a>
            <div class="collapse" id="submenu-inventory">
              <ul class="nav nav-sm flex-column">
                <li class="nav-item">
                  <a class="nav-link" href="add_inventory.php">
                    <i class="fas fa-pallet nav-icon text-primary"></i> Add Inventory
                  </a>
                </li>
                <li class="nav-item">
                  <a class="nav-link" href="inventory.php">
                    <i class="fas fa-sliders-h nav-icon text-primary"></i> Adjustments
                  </a>
                </li>
                <li class="nav-item">
                  <a class="nav-link" href="inventory_history.php">
                    <i class="fas fa-history text-primary"></i> History
                  </a>
                </li>
              </ul>
            </div>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="paymentMethod.php">
              <i class="fas fa-credit-card text-primary"></i> Payment Method
            </a>
          </li>
          <li class="nav-item">
            <a href="javascript:void(0);" class="nav-link" data-toggle="collapse" data-target="#submenu-distribution" aria-expanded="false" aria-controls="submenu-Returns">
              <i class="ni ni-credit-card text-primary"></i>
              <span class="menu-text">Distribution</span>
            </a>
            <div class="collapse" id="submenu-distribution">
              <ul class="nav nav-sm flex-column">
                <li class="nav-item">
                  <a class="nav-link" href="disDistributor.php">
                    <i class="fas fa-user-tie text-primary"></i> Distributor
                  </a>
                </li>
                <li class="nav-item">
                  <a class="nav-link" href="disCustomer.php">
                    <i class="fas fa-user text-primary"></i> Customer
                  </a>
                </li>
              </ul>
            </div>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="receipts.php">
              <i class="fas fa-file-invoice-dollar text-primary"></i> Receipts
            </a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="payments.php">
              <i class="fas fa-funnel-dollar text-primary"></i> Payments
            </a>
          </li>
          <li class="nav-item">
            <a href="javascript:void(0);" class="nav-link" data-toggle="collapse" data-target="#submenu-Returns" aria-expanded="false" aria-controls="submenu-Returns">
              <i class="fas fa-reply text-primary"></i>
              <span class="menu-text">Returns</span>
            </a>
            <div class="collapse" id="submenu-Returns">
              <ul class="nav nav-sm flex-column">
                <li class="nav-item">
                  <a class="nav-link" href="return.php">
                    <i class="fas fa-exchange-alt" style="color: #fb8640;"></i> Return
                  </a>
                </li>
                <li class="nav-item">
                  <a class="nav-link" href="return_receipts.php">
                    <i class="fas fa-file-alt" style="color: #fb8640;"></i> Return Receipts
                  </a>
                </li>
              </ul>
            </div>
          </li>
        </ul>
        <!-- Divider -->
        <hr class="my-3">
        <!-- Heading -->
        <h6 class="navbar-heading text-muted">Reporting</h6>
        <!-- Navigation -->
        <ul class="navbar-nav mb-md-3">
          <li class="nav-item">
            <a class="nav-link" href="salesReport.php">
              <i class="fas fa-user-tie"></i> Sales
            </a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="orders_reports.php">
              <i class="fas fa-shopping-basket"></i> Orders
            </a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="expense_report.php">
              <i class="fas fa-receipt"></i> Expense
            </a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="return_reports.php">
              <i class="fas fa-clipboard-list"></i> Return
            </a>
          </li>
        </ul>
        <hr class="my-3">
        <ul class="navbar-nav mb-md-3">
          <li class="nav-item">
            <a class="nav-link" href="logout.php">
              <i class="fas fa-sign-out-alt text-danger"></i> Log Out
            </a>
          </li>
        </ul>
      </div>
    </div>
  </nav>

<?php } ?>