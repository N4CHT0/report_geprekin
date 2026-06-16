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
                          <h6 class="m-0 font-weight-bold text-primary">Master Data Outlet</h6>
                          <div>
                              <button class="btn btn-sm btn-success" data-toggle="modal" data-target="#modalImport">
                                  <i class="fas fa-file-import"></i> Import Data
                              </button>
                              <button class="btn btn-sm btn-primary" data-toggle="modal" data-target="#modalTambah">
                                  <i class="fas fa-plus"></i> Tambah Data
                              </button>
                          </div>
                      </div>
                      <div class="card-body">
                          <div class="table-responsive">
                              <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                                  <thead>
                                      <tr>
                                          <th>No</th>
                                          <th>Nama Outlet</th>
                                          <th>Status</th>
                                          <th>Created At</th>
                                          <th>Action</th>
                                      </tr>
                                  </thead>
                                  <tbody>
                                      @forelse($outlets as $index => $outlet)
                                          <tr>
                                              <td>{{ $index + 1 }}</td>
                                              <td>{{ $outlet->nama_outlet }}</td>
                                              <td>{{ ucfirst($outlet->status) }}</td>
                                              <td>{{ $outlet->created_at ? $outlet->created_at->format('d M Y H:i') : '-' }}
                                              </td>
                                              <td>
                                                  <!-- Edit Button -->
                                                  <button class="btn btn-sm btn-warning" data-toggle="modal"
                                                      data-target="#modalEdit{{ $outlet->id }}">
                                                      <i class="fas fa-edit"></i> Ubah
                                                  </button>
                                                  <!-- Delete Form -->
                                                  <form action="{{ route('outlets.destroy', $outlet->id) }}"
                                                      method="POST" style="display:inline-block;">
                                                      @csrf
                                                      @method('DELETE')
                                                      <button class="btn btn-sm btn-danger"
                                                          onclick="return confirm('Yakin ingin hapus outlet ini?')">
                                                          <i class="fas fa-trash"></i> Hapus
                                                      </button>
                                                  </form>
                                              </td>
                                          </tr>
                                      @empty
                                          <tr>
                                              <td colspan="5" class="text-center">Data belum ada</td>
                                          </tr>
                                      @endforelse
                                  </tbody>
                              </table>
                          </div>
                      </div>
                  </div>

                  <!-- Modal Edit -->
                  @foreach ($outlets as $outlet)
                      <div class="modal fade" id="modalEdit{{ $outlet->id }}" tabindex="-1" role="dialog"
                          aria-labelledby="modalEditLabel{{ $outlet->id }}" aria-hidden="true">
                          <div class="modal-dialog" role="document">
                              <div class="modal-content">
                                  <form action="{{ route('outlets.update', $outlet->id) }}" method="POST">
                                      @csrf
                                      <!--@method('PUT') -->

                                      <div class="modal-header">
                                          <h5 class="modal-title">Edit Outlet</h5>
                                          <button type="button" class="close" data-dismiss="modal"
                                              aria-label="Close">
                                              <span aria-hidden="true">&times;</span>
                                          </button>
                                      </div>

                                      <div class="modal-body">
                                          <div class="form-group">
                                              <label>Nama Outlet</label>
                                              <input type="text" name="nama_outlet" class="form-control"
                                                  value="{{ $outlet->nama_outlet }}" required>
                                          </div>

                                          <div class="form-group">
                                              <label>Status</label>
                                              <select name="status" class="form-control" required>
                                                  <option value="existing"
                                                      {{ $outlet->status == 'existing' ? 'selected' : '' }}>Existing
                                                  </option>
                                                  <option value="go"
                                                      {{ $outlet->status == 'go' ? 'selected' : '' }}>Go</option>
                                                  <option value="tutup"
                                                      {{ $outlet->status == 'tutup' ? 'selected' : '' }}>Tutup</option>
                                              </select>
                                          </div>
                                          <div class="row">
                                              <div class="col-md-6 form-group">
                                                  <label>Latitude (Opsional)</label>
                                                  <input type="text" name="latitude" class="form-control" value="{{ $outlet->latitude ?? '' }}" placeholder="-7.250445">
                                              </div>
                                              <div class="col-md-6 form-group">
                                                  <label>Longitude (Opsional)</label>
                                                  <input type="text" name="longitude" class="form-control" value="{{ $outlet->longitude ?? '' }}" placeholder="112.768845">
                                              </div>
                                          </div>
                                      </div>

                                      <div class="modal-footer">
                                          <button type="button" class="btn btn-secondary"
                                              data-dismiss="modal">Batal</button>
                                          <button type="submit" class="btn btn-primary">Simpan</button>
                                      </div>
                                  </form>
                              </div>
                          </div>
                      </div>
                  @endforeach

                  <!-- Modal Tambah Data -->
                  <div class="modal fade" id="modalTambah" tabindex="-1" role="dialog"
                      aria-labelledby="modalTambahLabel" aria-hidden="true">
                      <div class="modal-dialog" role="document">
                          <div class="modal-content">
                              <form action="{{ route('outlets.store') }}" method="POST">
                                  @csrf
                                  <div class="modal-header">
                                      <h5 class="modal-title" id="modalTambahLabel">Tambah Data Outlet</h5>
                                      <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                          <span aria-hidden="true">&times;</span>
                                      </button>
                                  </div>
                                  <div class="modal-body">
                                      <div class="form-group">
                                          <label>Nama Outlet</label>
                                          <input type="text" name="nama_outlet" class="form-control" required>
                                      </div>
                                      <div class="form-group">
                                          <label>Status</label>
                                          <select name="status" class="form-control" required>
                                              <option value="existing">Existing</option>
                                              <option value="new">New</option>
                                          </select>
                                      </div>
                                      <div class="row">
                                          <div class="col-md-6 form-group">
                                              <label>Latitude (Opsional)</label>
                                              <input type="text" name="latitude" class="form-control" placeholder="-7.250445">
                                          </div>
                                          <div class="col-md-6 form-group">
                                              <label>Longitude (Opsional)</label>
                                              <input type="text" name="longitude" class="form-control" placeholder="112.768845">
                                          </div>
                                      </div>
                                  </div>
                                  <div class="modal-footer">
                                      <button type="button" class="btn btn-secondary"
                                          data-dismiss="modal">Batal</button>
                                      <button type="submit" class="btn btn-primary">Simpan</button>
                                  </div>
                              </form>

                          </div>
                      </div>
                  </div>

                  <!-- Modal Import Data -->
                  <div class="modal fade" id="modalImport" tabindex="-1" role="dialog"
                      aria-labelledby="modalImportLabel" aria-hidden="true">
                      <div class="modal-dialog" role="document">
                          <div class="modal-content">
                              <form action="{{ route('outlets.import') }}" method="POST"
                                  enctype="multipart/form-data">
                                  @csrf
                                  <div class="modal-header">
                                      <h5 class="modal-title" id="modalImportLabel">Import Data Outlet</h5>
                                      <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                          <span aria-hidden="true">&times;</span>
                                      </button>
                                  </div>
                                  <div class="modal-body">
                                      <div class="form-group">
                                          <label>Pilih File (Excel/CSV)</label>
                                          <input type="file" name="file" class="form-control"
                                              accept=".xlsx,.xls,.csv" required>
                                      </div>
                                  </div>
                                  <div class="modal-footer">
                                      <button type="button" class="btn btn-secondary"
                                          data-dismiss="modal">Batal</button>
                                      <button type="submit" class="btn btn-success">Import</button>
                                  </div>
                              </form>
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
                      pageLength: 5, // tampil 5 data per halaman
                      scrollX: true, // scroll horizontal jika perlu
                      scrollCollapse: true,
                      ordering: true, // bisa klik header untuk sort
                      paging: true, // aktifkan pagination
                      lengthChange: true, // bisa pilih jumlah per page
                      responsive: true
                  });
              });
          </script>
          @include('Temp.footer')
