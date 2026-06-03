      @include('Temp.header')
      <!-- Content Wrapper -->
      <div id="content-wrapper" class="d-flex flex-column">

          <!-- Main Content -->
          <div id="content">

              <!-- Topbar -->
              <nav class="navbar navbar-expand navbar-light bg-white topbar mb-4 static-top shadow">

                  <!-- Sidebar Toggle (Topbar) -->
                  <form class="form-inline">
                      <button id="sidebarToggleTop" class="btn btn-link d-md-none rounded-circle mr-3">
                          <i class="fa fa-bars"></i>
                      </button>
                  </form>

                  <!-- Topbar Navbar -->
                  <ul class="navbar-nav ml-auto">

                      <!-- Nav Item - Search Dropdown (Visible Only XS) -->
                      <li class="nav-item dropdown no-arrow d-sm-none">
                          <a class="nav-link dropdown-toggle" href="#" id="searchDropdown" role="button"
                              data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                              <i class="fas fa-search fa-fw"></i>
                          </a>
                          <!-- Dropdown - Messages -->
                          <div class="dropdown-menu dropdown-menu-right p-3 shadow animated--grow-in"
                              aria-labelledby="searchDropdown">
                              <form class="form-inline mr-auto w-100 navbar-search">
                                  <div class="input-group">
                                      <input type="text" class="form-control bg-light border-0 small"
                                          placeholder="Search for..." aria-label="Search"
                                          aria-describedby="basic-addon2">
                                      <div class="input-group-append">
                                          <button class="btn btn-primary" type="button">
                                              <i class="fas fa-search fa-sm"></i>
                                          </button>
                                      </div>
                                  </div>
                              </form>
                          </div>
                      </li>

                      <!-- Nav Item - Alerts -->
                      <li class="nav-item dropdown no-arrow mx-1">
                          <a class="nav-link dropdown-toggle" href="#" id="alertsDropdown" role="button"
                              data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                              <i class="fas fa-bell fa-fw"></i>
                              <!-- Counter - Alerts -->
                              <span class="badge badge-danger badge-counter">3+</span>
                          </a>
                          <!-- Dropdown - Alerts -->
                          <div class="dropdown-list dropdown-menu dropdown-menu-right shadow animated--grow-in"
                              aria-labelledby="alertsDropdown">
                              <h6 class="dropdown-header">
                                  Alerts Center
                              </h6>
                              <a class="dropdown-item d-flex align-items-center" href="#">
                                  <div class="mr-3">
                                      <div class="icon-circle bg-primary">
                                          <i class="fas fa-file-alt text-white"></i>
                                      </div>
                                  </div>
                                  <div>
                                      <div class="small text-gray-500">December 12, 2019</div>
                                      <span class="font-weight-bold">A new monthly report is ready to download!</span>
                                  </div>
                              </a>
                              <a class="dropdown-item d-flex align-items-center" href="#">
                                  <div class="mr-3">
                                      <div class="icon-circle bg-success">
                                          <i class="fas fa-donate text-white"></i>
                                      </div>
                                  </div>
                                  <div>
                                      <div class="small text-gray-500">December 7, 2019</div>
                                      $290.29 has been deposited into your account!
                                  </div>
                              </a>
                              <a class="dropdown-item d-flex align-items-center" href="#">
                                  <div class="mr-3">
                                      <div class="icon-circle bg-warning">
                                          <i class="fas fa-exclamation-triangle text-white"></i>
                                      </div>
                                  </div>
                                  <div>
                                      <div class="small text-gray-500">December 2, 2019</div>
                                      Spending Alert: We've noticed unusually high spending for your account.
                                  </div>
                              </a>
                              <a class="dropdown-item text-center small text-gray-500" href="#">Show All
                                  Alerts</a>
                          </div>
                      </li>

                      <!-- Nav Item - Messages -->
                      <li class="nav-item dropdown no-arrow mx-1">
                          <a class="nav-link dropdown-toggle" href="#" id="messagesDropdown" role="button"
                              data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                              <i class="fas fa-envelope fa-fw"></i>
                              <!-- Counter - Messages -->
                              <span class="badge badge-danger badge-counter">7</span>
                          </a>
                          <!-- Dropdown - Messages -->
                          <div class="dropdown-list dropdown-menu dropdown-menu-right shadow animated--grow-in"
                              aria-labelledby="messagesDropdown">
                              <h6 class="dropdown-header">
                                  Message Center
                              </h6>
                              <a class="dropdown-item d-flex align-items-center" href="#">
                                  <div class="dropdown-list-image mr-3">
                                      <img class="rounded-circle" src="img/undraw_profile_1.svg" alt="...">
                                      <div class="status-indicator bg-success"></div>
                                  </div>
                                  <div class="font-weight-bold">
                                      <div class="text-truncate">Hi there! I am wondering if you can help me with a
                                          problem I've been having.</div>
                                      <div class="small text-gray-500">Emily Fowler 路 58m</div>
                                  </div>
                              </a>
                              <a class="dropdown-item d-flex align-items-center" href="#">
                                  <div class="dropdown-list-image mr-3">
                                      <img class="rounded-circle" src="img/undraw_profile_2.svg" alt="...">
                                      <div class="status-indicator"></div>
                                  </div>
                                  <div>
                                      <div class="text-truncate">I have the photos that you ordered last month, how
                                          would you like them sent to you?</div>
                                      <div class="small text-gray-500">Jae Chun 路 1d</div>
                                  </div>
                              </a>
                              <a class="dropdown-item d-flex align-items-center" href="#">
                                  <div class="dropdown-list-image mr-3">
                                      <img class="rounded-circle" src="img/undraw_profile_3.svg" alt="...">
                                      <div class="status-indicator bg-warning"></div>
                                  </div>
                                  <div>
                                      <div class="text-truncate">Last month's report looks great, I am very happy with
                                          the progress so far, keep up the good work!</div>
                                      <div class="small text-gray-500">Morgan Alvarez 路 2d</div>
                                  </div>
                              </a>
                              <a class="dropdown-item d-flex align-items-center" href="#">
                                  <div class="dropdown-list-image mr-3">
                                      <img class="rounded-circle" src="https://source.unsplash.com/Mv9hjnEUHR4/60x60"
                                          alt="...">
                                      <div class="status-indicator bg-success"></div>
                                  </div>
                                  <div>
                                      <div class="text-truncate">Am I a good boy? The reason I ask is because someone
                                          told me that people say this to all dogs, even if they aren't good...</div>
                                      <div class="small text-gray-500">Chicken the Dog 路 2w</div>
                                  </div>
                              </a>
                              <a class="dropdown-item text-center small text-gray-500" href="#">Read More
                                  Messages</a>
                          </div>
                      </li>

                      <div class="topbar-divider d-none d-sm-block"></div>

                      <!-- Nav Item - User Information -->
                      <li class="nav-item dropdown no-arrow">
                          <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button"
                              data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                              <span class="mr-2 d-none d-lg-inline text-gray-600 small">Douglas McGee</span>
                              <img class="img-profile rounded-circle" src="img/undraw_profile.svg">
                          </a>
                          <!-- Dropdown - User Information -->
                          <div class="dropdown-menu dropdown-menu-right shadow animated--grow-in"
                              aria-labelledby="userDropdown">
                              <a class="dropdown-item" href="#">
                                  <i class="fas fa-user fa-sm fa-fw mr-2 text-gray-400"></i>
                                  Profile
                              </a>
                              <a class="dropdown-item" href="#">
                                  <i class="fas fa-cogs fa-sm fa-fw mr-2 text-gray-400"></i>
                                  Settings
                              </a>
                              <a class="dropdown-item" href="#">
                                  <i class="fas fa-list fa-sm fa-fw mr-2 text-gray-400"></i>
                                  Activity Log
                              </a>
                              <div class="dropdown-divider"></div>
                              <a class="dropdown-item" href="#" data-toggle="modal"
                                  data-target="#logoutModal">
                                  <i class="fas fa-sign-out-alt fa-sm fa-fw mr-2 text-gray-400"></i>
                                  Logout
                              </a>
                          </div>
                      </li>

                  </ul>
              </nav>
              <!-- End of Topbar -->

              <!-- Begin Page Content -->
              <div class="container-fluid">
                  <!-- DataTales Example -->
                  <div class="card shadow mb-4">

                      <div class="card-header py-3 d-flex justify-content-between align-items-center">
                          <h6 class="m-0 font-weight-bold text-primary">Laporan Cost </h6>
                      </div>
                      <div class="card-body">
                          <!-- 馃敼 Filter Bulan & Tahun -->
                          <form method="GET" action="{{ route('laporan.laporanQCR') }}" class="mb-4">
                              <div class="row g-3 align-items-end">

                                  <!-- Input Bulan & Tahun -->
                                  <div class="col-12 col-md-3">
                                      <label for="bulan_tahun" class="form-label fw-bold">Bulan & Tahun</label>
                                      <input type="month" name="bulan_tahun" id="bulan_tahun" value=""
                                          class="form-control shadow-sm">
                                  </div>

                                  <!-- Tombol Filter & Reset -->
                                  <div class="col-12 col-md-4 d-flex gap-2">
                                      <button type="submit" class="btn btn-primary w-100 mr-2">
                                          <i class="fas fa-search"></i> Filter
                                      </button>
                                      <a href="{{ route('laporan.laporanQCR') }}" class="btn btn-secondary w-100">
                                          <i class="fas fa-sync"></i> Reset
                                      </a>
                                  </div>

                                  <!-- Export & Import -->
                                  <div class="col-12 col-md-5 d-flex gap-2 justify-content-md-end">
                                      <a href="" class="btn btn-success mr-2">
                                          <i class="fas fa-file-excel"></i> Export
                                      </a>

                                      <form action="" method="POST" enctype="multipart/form-data"
                                          class="d-inline">
                                          @csrf
                                          <label class="btn btn-info mb-0">
                                              <i class="fas fa-file-upload"></i> Import
                                              <input type="file" name="file" onchange="this.form.submit()"
                                                  hidden>
                                          </label>
                                      </form>
                                  </div>
                              </div>
                          </form>

                          <style>
                              /* Container tabel */
                              .table-container {
                                  overflow-x: auto;
                                  border: 1px solid #dee2e6;
                                  border-radius: 6px;
                                  margin-top: 15px;
                                  background: #fff;
                                  padding: 10px;
                              }

                              /* Base table */
                              table.office-table {
                                  border-collapse: collapse;
                                  width: 100%;
                                  min-width: 1500px;
                                  /* untuk tabel panjang */
                                  font-size: 13px;
                                  font-family: "Segoe UI", Tahoma, Arial, sans-serif;
                              }

                              /* Header */
                              table.office-table thead th {
                                  background: #f1f3f5;
                                  color: #212529;
                                  font-weight: 600;
                                  padding: 8px 12px;
                                  border: 1px solid #dee2e6;
                                  text-align: center;
                                  vertical-align: middle;
                                  white-space: nowrap;
                              }

                              /* Body */
                              table.office-table td {
                                  padding: 8px 12px;
                                  border: 1px solid #dee2e6;
                                  text-align: center;
                                  vertical-align: middle;
                                  white-space: nowrap;
                              }

                              /* Kolom khusus */
                              .col-no {
                                  width: 40px;
                                  font-weight: 600;
                              }

                              .col-outlet {
                                  width: 160px;
                                  text-align: left;
                                  font-weight: 600;
                                  background: #fafafa;
                              }

                              .col-menu {
                                  width: 160px;
                                  text-align: left;
                                  font-weight: 500;
                              }

                              .col-unit,
                              .col-harga {
                                  width: 90px;
                                  font-weight: 500;
                              }

                              /* Striping */
                              table.office-table tbody tr:nth-of-type(odd) {
                                  background-color: #fbfbfb;
                              }

                              /* Hover */
                              table.office-table tbody tr:hover {
                                  background-color: #f5faff;
                                  transition: 0.15s;
                              }
                          </style>

                          <div class="table-container">
                              <table id="laporanTable" class="office-table table table-striped nowrap"
                                  style="width:100%">
                                  <thead>
                                      <tr>
                                          <th class="col-no">No</th>
                                          <th class="col-outlet">Nama Outlet</th>
                                          <th class="col-menu">Menu</th>
                                          <th class="col-unit">Unit Sold</th>
                                          <th class="col-harga">Harga</th>
                                          <th>AB</th>
                                          <th>AK</th>
                                          <th>Marinasi</th>
                                          <th>Rice</th>
                                          <th>Saos Sambal</th>
                                          <th>Saos Tomat</th>
                                          <th>Breader</th>
                                          <th>Sambal</th>
                                          <th>Saos Gangnam</th>
                                          <th>Lunch Box</th>
                                          <th>Paper Bag</th>
                                          <th>Wrap Nasi</th>
                                          <th>Kebab</th>
                                          <th>Kebab Paper</th>
                                          <th>Air Mineral</th>
                                          <th>S-Tee</th>
                                          <th>Cup Sambel</th>
                                          <th>Cup 35</th>
                                      </tr>
                                  </thead>
                                  <tbody>
                                      <!-- Outlet A punya 2 menu -->
                                      <tr>
                                          <td rowspan="2">1</td>
                                          <td rowspan="2">Outlet A</td>
                                          <td>Nasi Goreng</td>
                                          <td>120</td>
                                          <td>20.000</td>
                                          <td>12</td>
                                          <td>6</td>
                                          <td>10</td>
                                          <td>15</td>
                                          <td>9</td>
                                          <td>8</td>
                                          <td>11</td>
                                          <td>7</td>
                                          <td>6</td>
                                          <td>13</td>
                                          <td>9</td>
                                          <td>10</td>
                                          <td>8</td>
                                          <td>4</td>
                                          <td>25</td>
                                          <td>15</td>
                                          <td>7</td>
                                          <td>3</td>
                                      </tr>
                                      <tr>
                                          <td>Mie Ayam</td>
                                          <td>95</td>
                                          <td>18.000</td>
                                          <td>10</td>
                                          <td>5</td>
                                          <td>12</td>
                                          <td>14</td>
                                          <td>8</td>
                                          <td>7</td>
                                          <td>9</td>
                                          <td>6</td>
                                          <td>5</td>
                                          <td>12</td>
                                          <td>8</td>
                                          <td>10</td>
                                          <td>6</td>
                                          <td>3</td>
                                          <td>20</td>
                                          <td>12</td>
                                          <td>6</td>
                                          <td>2</td>
                                      </tr>
                                      <tr>
                                          <td rowspan="2">2</td>
                                          <td rowspan="2">Outlet B</td>
                                          <td>Nasi Goreng</td>
                                          <td>120</td>
                                          <td>20.000</td>
                                          <td>12</td>
                                          <td>6</td>
                                          <td>10</td>
                                          <td>15</td>
                                          <td>9</td>
                                          <td>8</td>
                                          <td>11</td>
                                          <td>7</td>
                                          <td>6</td>
                                          <td>13</td>
                                          <td>9</td>
                                          <td>10</td>
                                          <td>8</td>
                                          <td>4</td>
                                          <td>25</td>
                                          <td>15</td>
                                          <td>7</td>
                                          <td>3</td>
                                      </tr>
                                      <tr>
                                          <td>Mie Ayam</td>
                                          <td>95</td>
                                          <td>18.000</td>
                                          <td>10</td>
                                          <td>5</td>
                                          <td>12</td>
                                          <td>14</td>
                                          <td>8</td>
                                          <td>7</td>
                                          <td>9</td>
                                          <td>6</td>
                                          <td>5</td>
                                          <td>12</td>
                                          <td>8</td>
                                          <td>10</td>
                                          <td>6</td>
                                          <td>3</td>
                                          <td>20</td>
                                          <td>12</td>
                                          <td>6</td>
                                          <td>2</td>
                                      </tr>
                                  </tbody>
                              </table>
                          </div>

                          <div class="table-container">
                              <table class="office-table table table-bordered table-striped nowrap"
                                  style="width:100%">
                                  <thead>
                                      <tr>
                                          <th style="width: 160px;">Keterangan</th>
                                          <th>AB</th>
                                          <th>AK</th>
                                          <th>Marinasi</th>
                                          <th>Rice</th>
                                          <th>Saos Sambal</th>
                                          <th>Saos Tomat</th>
                                          <th>Breader</th>
                                          <th>Sambal</th>
                                          <th>Saos Gangnam</th>
                                          <th>Lunch Box</th>
                                          <th>Paper Bag</th>
                                          <th>Wrap Nasi</th>
                                          <th>Kebab</th>
                                          <th>Kebab Paper</th>
                                          <th>Air Mineral</th>
                                          <th>S-Tee</th>
                                          <th>Cup Sambel</th>
                                          <th>Cup 35</th>
                                      </tr>
                                  </thead>
                                  <tbody>
                                      <tr>
                                          <td><strong>Price/Unit</strong></td>
                                          <td>Rp3.503,00</td>
                                          <td>Rp3.503,00</td>
                                          <td>Rp30,80</td>
                                          <td>Rp13,00</td>
                                          <td>Rp255,21</td>
                                          <td>Rp212,50</td>
                                          <td>Rp18,36</td>
                                          <td>Rp55,00</td>
                                          <td>Rp55,00</td>
                                          <td>Rp850,00</td>
                                          <td>Rp220,00</td>
                                          <td>Rp65,00</td>
                                          <td>Rp4.000,00</td>
                                          <td>Rp350,00</td>
                                          <td>Rp1.354,17</td>
                                          <td>Rp1.500,00</td>
                                          <td>Rp157,50</td>
                                          <td>Rp190,42</td>
                                      </tr>
                                      <tr>
                                          <td><strong>HPP</strong></td>
                                          <td>2</td>
                                          <td>2</td>
                                          <td>2</td>
                                          <td>2</td>
                                          <td>2</td>
                                          <td>2</td>
                                          <td>2</td>
                                          <td>2</td>
                                          <td>2</td>
                                          <td>2</td>
                                          <td>2</td>
                                          <td>2</td>
                                          <td>2</td>
                                          <td>2</td>
                                          <td>2</td>
                                          <td>2</td>
                                          <td>2</td>
                                          <td>2</td>
                                      </tr>
                                      <tr>
                                          <td><strong>Usage</strong></td>
                                          <td>4.015,00</td>
                                          <td>2.944,00</td>
                                          <td>#REF!</td>
                                          <td>419.000,00</td>
                                          <td>2.658,00</td>
                                          <td>2.850,00</td>
                                          <td>236.500,00</td>
                                          <td>97.000,00</td>
                                          <td>14.000,00</td>
                                          <td>4.469,00</td>
                                          <td>1.586,00</td>
                                          <td>4.671,00</td>
                                          <td>-</td>
                                          <td>-</td>
                                          <td>-</td>
                                          <td>-</td>
                                          <td>3.936,00</td>
                                          <td>10,00</td>
                                      </tr>
                                      <tr>
                                          <td><strong>Diff</strong></td>
                                          <td>2</td>
                                          <td>2</td>
                                          <td>2</td>
                                          <td>2</td>
                                          <td>Rp-</td>
                                          <td>2</td>
                                          <td>Rp-</td>
                                          <td>Rp-</td>
                                          <td>2</td>
                                          <td>2</td>
                                          <td>Rp-</td>
                                          <td>2</td>
                                          <td>2</td>
                                          <td>2</td>
                                          <td>2</td>
                                          <td>2</td>
                                          <td>2</td>
                                          <td>2</td>
                                      </tr>
                                  </tbody>
                              </table>
                          </div>
                          <div class="table-container">
                              <table class="table table-bordered table-striped mt-4"
                                  style="width:50%; font-size:14px;">
                                  <tbody>
                                      <tr>
                                          <td><strong>Total Sales</strong></td>
                                          <td class="text-end">144.301.000</td>
                                          <td class="text-end">100%</td>
                                      </tr>
                                      <tr>
                                          <td><strong>Target</strong></td>
                                          <td class="text-end">#DIV/0!</td>
                                          <td class="text-end">-</td>
                                      </tr>
                                      <tr>
                                          <td><strong>HPP</strong></td>
                                          <td class="text-end">88.121.209</td>
                                          <td class="text-end">61,1%</td>
                                      </tr>
                                      <tr>
                                          <td><strong>Sales Reguler</strong></td>
                                          <td class="text-end">144.280.000</td>
                                          <td class="text-end">100,0%</td>
                                      </tr>
                                      <tr>
                                          <td><strong>Online Food</strong></td>
                                          <td class="text-end">21.000</td>
                                          <td class="text-end">0,0%</td>
                                      </tr>
                                      <tr>
                                          <td><strong>GP</strong></td>
                                          <td class="text-end">56.179.791</td>
                                          <td class="text-end">38,9%</td>
                                      </tr>
                                      <tr>
                                          <td><strong>Waste</strong></td>
                                          <td class="text-end">-58.908</td>
                                          <td class="text-end">0,0%</td>
                                      </tr>
                                      <tr>
                                          <td><strong>Selisih Persediaan</strong></td>
                                          <td class="text-end">2.429.198</td>
                                          <td class="text-end">1,7%</td>
                                      </tr>
                                  </tbody>
                              </table>
                          </div>
                      </div>
                  </div>
              </div>
              <!-- /.container-fluid -->

          </div>
          <!-- End of Main Content -->
          <script>
              $(document).ready(function() {
                  $('#laporanTable').DataTable({
                      scrollX: true,
                      fixedColumns: {
                          leftColumns: 2
                      },
                      pageLength: 10,
                      lengthMenu: [10, 25, 50, 100, 500, 1000],
                      responsive: true,
                      rowGroup: {
                          dataSrc: 1 // kolom ke-2 (Nama Outlet) → otomatis dikelompokkan
                      },
                      language: {
                          search: "_INPUT_",
                          searchPlaceholder: "Cari data...",
                          lengthMenu: "Tampilkan _MENU_ data",
                          info: "Menampilkan _START_ - _END_ dari _TOTAL_ data",
                          paginate: {
                              previous: "←",
                              next: "→"
                          }
                      }
                  });
              });
          </script>
          @include('Temp.footer')
