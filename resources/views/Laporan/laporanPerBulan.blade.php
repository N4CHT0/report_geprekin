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
                          <h6 class="m-0 font-weight-bold text-primary">Laporan Data Per Bulan</h6>
                      </div>
                      <div class="card-body">
                          <!-- Filter Bulan & Tahun -->
                          <form method="GET" action="" class="mb-3" id="formLaporanBulan">
                              <div class="row">
                                  <!-- Input Bulan & Tahun -->
                                  <div class="col-12 col-md-3 mb-2">
                                      <label for="bulan_tahun" class="form-label fw-bold">Bulan & Tahun</label>
                                      <input type="month" name="bulan_tahun" id="bulan_tahun"
                                          value="{{ $bulanTahun ?? '' }}" class="form-control">
                                  </div>

                                  <!-- Tombol Filter & Reset -->
                                  <div class="col-12 d-flex flex-wrap align-items-end gap-2">
                                      <!-- Filter & Reset -->
                                      <div class="d-flex flex-wrap gap-2">
                                          <button type="submit"
                                              class="btn btn-outline-primary d-flex align-items-center mr-2">
                                              <i class="fas fa-search mr-2 me-2"></i> Filter
                                          </button>
                                          <a href="{{ route('laporan.laporanPerBulan') }}"
                                              class="btn btn-outline-secondary d-flex align-items-center mr-2">
                                              <i class="fas fa-sync-alt mr-2 me-2"></i> Reset
                                          </a>
                                      </div>

                                      <!-- Export di kanan -->
                                      <a href=""
                                          class="btn btn-outline-success d-flex align-items-center ms-auto">
                                          <i class="fas fa-file-excel mr-2 me-2"></i> Export
                                      </a>
                                  </div>
                              </div>
                          </form>

                          <script>
                              document.getElementById('formLaporanBulan').addEventListener('submit', function(e) {
                                  const bulanTahun = document.getElementById('bulan_tahun').value;
                                  if (!bulanTahun) {
                                      e.preventDefault(); // hentikan submit
                                      alert('Harap pilih bulan dan tahun terlebih dahulu!');
                                  }
                              });
                          </script>

                          <style>
                              /* --- Tabel dasar biar tidak patah baris --- */
                              table.dataTable td,
                              table.dataTable th {
                                  white-space: nowrap;
                                  vertical-align: middle;
                              }

                              /* --- Pastikan kolom fixed mengikuti striping bootstrap --- */
                              .DTFC_LeftBodyWrapper table.table-striped tbody tr:nth-of-type(odd),
                              .DTFC_LeftBodyWrapper table.table-striped tbody tr:nth-of-type(even),
                              .DTFC_LeftBodyWrapper table.table-striped tbody tr {
                                  background-color: inherit !important;
                              }

                              /* --- Pastikan header fixed juga ikut warna default --- */
                              .DTFC_LeftHeadWrapper table thead tr th {
                                  background-color: inherit !important;
                              }

                              /* --- Sedikit shadow di sisi kanan fixed column biar jelas --- */
                              .DTFC_LeftBodyWrapper,
                              .DTFC_LeftHeadWrapper {
                                  box-shadow: 2px 0 6px rgba(0, 0, 0, 0.08);
                                  background-color: #fff;
                                  /* base putih, tapi tr mewarisi striping */
                              }

                              /* --- Kalau mau lebih jelas stripingnya (override bootstrap default) --- */
                              .table-striped tbody tr:nth-of-type(odd) {
                                  background-color: #f8f9fa !important;
                                  /* abu-abu muda */
                              }

                              .table-striped tbody tr:nth-of-type(even) {
                                  background-color: #ffffff !important;
                                  /* putih */
                              }
                          </style>
                          <!-- Tabel -->
                          <div class="table-responsive">
                              <table class="table table-bordered table-striped table-hover" id="dataTable"
                                  width="100%">
                                  <thead>
                                      <tr>
                                          <th rowspan="2">No</th>
                                          <th rowspan="2">Outlet</th>
                                          <th rowspan="2">Kode</th>
                                          @for ($d = 1; $d <= 31; $d++)
                                              <th colspan="3">{{ str_pad($d, 2, '0', STR_PAD_LEFT) }}</th>
                                          @endfor
                                      </tr>
                                      <tr>
                                          @for ($d = 1; $d <= 31; $d++)
                                              <th>Sales</th>
                                              <th>CU</th>
                                              <th>AC</th>
                                          @endfor
                                      </tr>
                                  </thead>
                                  <tbody>
                                      <tr>
                                          <td>{{ $index + 1 }}</td>
                                          <td>{{ $row['nama_outlet'] }}</td>
                                          <td>{{ $row['kode_outlet'] ?? '' }}</td>
                                          @for ($d = 1; $d <= 31; $d++)
                                              <td>{{ number_format($row['hari'][$d]['sales']) }}</td>
                                              <td>{{ $row['hari'][$d]['cu'] }}</td>
                                              <td>{{ number_format($row['hari'][$d]['ac']) }}</td>
                                          @endfor
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
                  $('#dataTable').DataTable({
                      scrollX: true,
                      scrollCollapse: true,
                      paging: true, // ✅ aktifkan paging
                      pageLength: 5, // default tampil 25
                      lengthMenu: [10, 25, 50, 100, 200, 500, 800], // opsi "Show entries"
                      fixedColumns: {
                          leftColumns: 3 // fix No, Outlet, Kode
                      }
                  });
              });
          </script>
      @include('Temp.footer')
