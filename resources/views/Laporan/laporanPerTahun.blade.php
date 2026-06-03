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
                          <h6 class="m-0 font-weight-bold text-primary">Laporan Data Per Tahun</h6>
                      </div>
                      <div class="card-body">
                          <form method="GET" action="{{ route('laporan.laporanPerTahun') }}" class="mb-3">
                              <div class="row">
                                  <!-- Select Tahun -->
                                  <div class="col-12 col-md-3 mb-2">
                                      <label for="tahun">Tahun</label>
                                      <select name="tahun" id="tahun" class="form-control">
                                          @php
                                              $currentYear = date('Y');
                                              $startYear = $currentYear - 5;
                                          @endphp
                                          @for ($y = $currentYear; $y >= $startYear; $y--)
                                              <option value="{{ $y }}" {{ $tahun == $y ? 'selected' : '' }}>
                                                  {{ $y }}
                                              </option>
                                          @endfor
                                      </select>
                                  </div>

                                  <!-- Tombol Filter & Reset -->
                               <!-- Tombol Filter & Reset -->
                                  <div class="col-12 d-flex flex-wrap align-items-end gap-2">
                                      <!-- Filter & Reset -->
                                      <div class="d-flex flex-wrap gap-2">
                                          <button type="submit"
                                              class="btn btn-outline-primary d-flex align-items-center mr-2">
                                              <i class="fas fa-search me-2 mr-2"></i> Filter
                                          </button>
                                          <a href="{{ route('laporan.laporanPerBulan') }}"
                                              class="btn btn-outline-secondary d-flex align-items-center mr-2">
                                              <i class="fas fa-sync-alt mr-2 me-2"></i> Reset
                                          </a>
                                      </div>

                                      <!-- Export di kanan -->
                                      <a href="" class="btn btn-outline-success d-flex align-items-center ms-auto">
                                          <i class="fas fa-file-excel mr-2 me-2"></i> Export
                                      </a>
                                  </div>
                              </div>
                          </form>

                          <style>
                              /* Kolom tidak patah baris & padding */
                              table.dataTable td,
                              table.dataTable th {
                                  white-space: nowrap;
                                  vertical-align: middle;
                                  padding: 6px 8px;
                              }

                              /* Kolom fixed ikut striping */
                              .DTFC_LeftBodyWrapper table.table-striped tbody tr:nth-of-type(odd),
                              .DTFC_LeftBodyWrapper table.table-striped tbody tr:nth-of-type(even),
                              .DTFC_LeftBodyWrapper table.table-striped tbody tr {
                                  background-color: inherit !important;
                              }

                              /* Header fixed */
                              .DTFC_LeftHeadWrapper table thead tr th {
                                  background-color: inherit !important;
                              }

                              /* Shadow tipis kolom fixed */
                              .DTFC_LeftBodyWrapper,
                              .DTFC_LeftHeadWrapper {
                                  box-shadow: 2px 0 6px rgba(0, 0, 0, 0.08);
                                  background-color: #fff;
                              }

                              /* Striping Bootstrap */
                              .table-striped tbody tr:nth-of-type(odd) {
                                  background-color: #f8f9fa !important;
                              }

                              .table-striped tbody tr:nth-of-type(even) {
                                  background-color: #ffffff !important;
                              }

                              /* Responsif mobile */
                              @media (max-width: 767.98px) {
                                  table.dataTable {
                                      width: 100% !important;
                                  }

                                  table.dataTable td,
                                  table.dataTable th {
                                      font-size: 12px;
                                      padding: 4px 6px;
                                  }

                                  .DTFC_LeftBodyWrapper,
                                  .DTFC_LeftHeadWrapper {
                                      box-shadow: none;
                                  }
                              }
                          </style>

                          <div class="table-responsive">
                              <table class="table table-bordered table-striped table-hover" id="dataTable"
                                  width="100%">
                                  <thead>
                                      <tr>
                                          <th style="min-width:50px;" rowspan="2">No</th>
                                          <th style="min-width:150px;" rowspan="2">Outlet</th>
                                          <th style="min-width:80px;" rowspan="2">Kode</th>
                                          @for ($m = 1; $m <= 12; $m++)
                                              <th style="min-width:70px;" colspan="3">
                                                  {{ \Carbon\Carbon::create()->month($m)->format('M') }}</th>
                                          @endfor
                                          <th colspan="3" style="min-width:80px;">Total</th>
                                      </tr>
                                      <tr>
                                          @for ($m = 1; $m <= 12; $m++)
                                              <th style="min-width:70px;">Sales</th>
                                              <th style="min-width:50px;">CU</th>
                                              <th style="min-width:60px;">AC</th>
                                          @endfor
                                          <th style="min-width:70px;">Sales</th>
                                          <th style="min-width:50px;">CU</th>
                                          <th style="min-width:60px;">AC</th>
                                      </tr>
                                  </thead>
                                  <tbody>
                                      @foreach ($laporan as $id => $data)
                                          @php
                                              $totalSales = $totalCU = 0;
                                          @endphp
                                          <tr>
                                              <td>{{ $loop->iteration }}</td>
                                              <td>{{ $data['nama_outlet'] }}</td>
                                              <td>{{ $data['kode_outlet'] ?: '-' }}</td>
                                              @for ($m = 1; $m <= 12; $m++)
                                                  <td>{{ number_format($data['bulan'][$m]['sales'], 0, ',', '.') }}</td>
                                                  <td>{{ $data['bulan'][$m]['cu'] }}</td>
                                                  <td>{{ number_format($data['bulan'][$m]['ac'], 0, ',', '.') }}</td>
                                                  @php
                                                      $totalSales += $data['bulan'][$m]['sales'];
                                                      $totalCU += $data['bulan'][$m]['cu'];
                                                  @endphp
                                              @endfor
                                              <td>{{ number_format($totalSales, 0, ',', '.') }}</td>
                                              <td>{{ $totalCU }}</td>
                                              <td>{{ $totalCU > 0 ? number_format(round($totalSales / $totalCU), 0, ',', '.') : 0 }}
                                              </td>
                                          </tr>
                                      @endforeach
                                  </tbody>
                                  <tfoot>
                                      <tr>
                                          <!-- Merge 1 sel untuk label Grand Total -->
                                          <th colspan="2" style="text-align:right;">Grand Total</th>
                                          <th></th> <!-- Kode tetap kosong -->

                                          @for ($m = 1; $m <= 12; $m++)
                                              <th>{{ number_format($grandTotal['sales'][$m], 0, ',', '.') }}</th>
                                              <th>{{ $grandTotal['cu'][$m] }}</th>
                                              <th>{{ number_format($grandTotal['ac'][$m], 0, ',', '.') }}</th>
                                          @endfor

                                          <!-- Total akhir -->
                                          <th>{{ number_format($grandTotal['totalSales'], 0, ',', '.') }}</th>
                                          <th>{{ $grandTotal['totalCU'] }}</th>
                                          <th>{{ number_format($grandTotal['totalAC'], 0, ',', '.') }}</th>
                                      </tr>
                                  </tfoot>

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
                  var table = $('#dataTable').DataTable({
                      scrollX: true,
                      scrollCollapse: true,
                      paging: true,
                      pageLength: 5,
                      ordering: false,
                      fixedColumns: {
                          left: 2 // No + Outlet tetap menempel
                      }
                  });
              });
          </script>
          @include('Temp.footer')
