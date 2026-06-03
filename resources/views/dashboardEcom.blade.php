      @include('Temp.header')
      <!-- Content Wrapper -->
      <div id="content-wrapper" class="d-flex flex-column">

          <!-- Main Content -->
          <div id="content">

              <!-- Topbar -->
              <nav class="navbar navbar-expand navbar-light bg-white topbar mb-4 static-top shadow">

                  <!-- Sidebar Toggle (Topbar) -->
                  <button id="sidebarToggleTop" class="btn btn-link d-md-none rounded-circle mr-3">
                      <i class="fa fa-bars"></i>
                  </button>

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
                                      <span class="font-weight-bold">A new monthly report is ready to
                                          download!</span>
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
                                      <div class="text-truncate">Last month's report looks great, I am very happy
                                          with
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
                                      <div class="text-truncate">Am I a good boy? The reason I ask is because
                                          someone
                                          told me that people say this to all dogs, even if they aren't good...
                                      </div>
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

                  <!-- Page Heading -->
                  <div class="d-sm-flex align-items-center justify-content-between mb-4">
                      <h1 class="h3 mb-0 text-gray-800">Dashboard</h1>
                  </div>

                  <!-- Filter Card -->
                  <div class="card shadow mb-4">
                      <div class="card-body">
                          <form action="{{ route('sales.ecom.dashboard') }}" method="GET">
                              <div class="form-row">
                                  <div class="form-group col-md-3">
                                      <label for="tanggal_awal">Mulai</label>
                                      <input type="date" name="tanggal_awal" id="tanggal_awal"
                                          class="form-control" value="{{ request('tanggal_awal') }}">
                                  </div>
                                  <div class="form-group col-md-3">
                                      <label for="tanggal_akhir">Sampai</label>
                                      <input type="date" name="tanggal_akhir" id="tanggal_akhir"
                                          class="form-control" value="{{ request('tanggal_akhir') }}">
                                  </div>
                                  <div class="form-group col-md-3">
                                      <label for="outlet">Outlet</label>
                                      <select name="outlet" id="outlet" class="form-control">
                                          <option value="">Semua Outlet</option>
                                          @foreach ($outlets as $id => $nama)
                                              <option value="{{ $nama }}"
                                                  {{ request('outlet') == $nama ? 'selected' : '' }}>
                                                  {{ $nama }}
                                              </option>
                                          @endforeach
                                      </select>
                                  </div>

                                  <div class="form-group col-md-3 d-flex align-items-end">
                                      <button type="submit" class="btn btn-primary btn-block">
                                          <i class="fas fa-filter"></i> Tampilkan
                                      </button>
                                  </div>
                              </div>
                          </form>
                      </div>
                  </div>

                  <!-- Content Card Row -->
                  <div class="row">
                      @php
                          $platformColors = [
                              'shopeefood' => 'primary',
                              'grabfood' => 'success',
                              'gofood' => 'info',
                          ];
                      @endphp

                      @foreach (['shopeefood', 'grabfood', 'gofood'] as $plat)
                          <div class="col-xl-4 col-md-6 mb-4">
                              <div class="card border-left-{{ $platformColors[$plat] }} shadow h-100 py-2">
                                  <div class="card-body">
                                      <h6
                                          class="text-xs font-weight-bold text-{{ $platformColors[$plat] }} text-uppercase mb-3">
                                          {{ ucfirst($plat) }}
                                      </h6>
                                      <div class="mb-2">
                                          <span class="text-xs font-weight-bold text-gray-600">Omset / Sales:</span>
                                          <span class="font-weight-bold text-gray-800">Rp
                                              {{ number_format($totalOmsetPerPlat[$plat] ?? 0, 0, ',', '.') }}</span>
                                      </div>
                                      <div class="mb-2">
                                          <span class="text-xs font-weight-bold text-gray-600">CU:</span>
                                          <span
                                              class="font-weight-bold text-gray-800">{{ number_format($totalCUPerPlat[$plat] ?? 0, 0, ',', '.') }}</span>
                                      </div>
                                      <div class="mb-2">
                                          <span class="text-xs font-weight-bold text-gray-600">Target:</span>
                                          <span class="font-weight-bold text-gray-800">Rp
                                              {{ number_format($targets[$plat] ?? 0, 0, ',', '.') }}</span>
                                      </div>
                                      <div>
                                          <span class="text-xs font-weight-bold text-gray-600">Average:</span>
                                          <span
                                              class="font-weight-bold text-gray-800">{{ number_format($averages[$plat] ?? 0, 0, ',', '.') }}</span>
                                      </div>
                                  </div>
                              </div>
                          </div>
                      @endforeach
                  </div>

                  <!-- Content Grafik Row -->
                  @if ($filterApplied)
                      <div class="row">
                          <!-- Omset -->
                          <div class="col-12 col-md-6 mb-4">
                              <div class="card shadow h-100">
                                  <div class="card-header py-3 d-flex justify-content-between align-items-center">
                                      <h6 class="m-0 font-weight-bold text-primary">Grafik Omset</h6>
                                      <span class="m-0 font-weight-bold text-primary">
                                          Total Omset: Rp {{ number_format($totalOmset, 0, ',', '.') }}
                                      </span>
                                  </div>
                                  <div class="card-body">
                                      <canvas id="grafikOmset"></canvas>
                                  </div>
                              </div>
                          </div>

                          <!-- Customer Unit -->
                          <div class="col-12 col-md-6 mb-4">
                              <div class="card shadow h-100">
                                  <div class="card-header py-3 d-flex justify-content-between align-items-center">
                                      <h6 class="m-0 font-weight-bold text-primary">Grafik Customer Unit</h6>
                                      <span class="m-0 font-weight-bold text-primary">
                                          Total CU: {{ number_format($totalCU, 0, ',', '.') }}
                                      </span>
                                  </div>
                                  <div class="card-body">
                                      <canvas id="grafikCU"></canvas>
                                  </div>
                              </div>
                          </div>

                          <!-- Transaksi per Jam -->
                          <div class="col-12 col-md-6 mb-4">
                              <div class="card shadow h-100">
                                  <div class="card-header py-3">
                                      <h6 class="m-0 font-weight-bold text-primary">Grafik Transaksi Perjam</h6>
                                  </div>
                                  <div class="card-body">
                                      <canvas id="grafikTransaksi"></canvas>
                                  </div>
                              </div>
                          </div>

                          <!-- Pareto -->
                          <div class="col-12 col-md-6 mb-4">
                              <div class="card shadow h-100">
                                  <div class="card-header py-3">
                                      <h6 class="m-0 font-weight-bold text-primary">Grafik Pareto</h6>
                                  </div>
                                  <div class="card-body">
                                      <canvas id="grafikPareto"></canvas>
                                  </div>
                              </div>
                          </div>
                      </div>
                  @endif

              </div>
              <!-- /.container-fluid -->

          </div>
          <!-- End of Main Content -->

          @if ($filterApplied)
              <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
              <script>
                  function getColors(data, up = 'blue', down = 'red') {
                      return data.map((v, i) => i === 0 ? up : (v >= data[i - 1] ? up : down));
                  }

                  function formatNumber(value, isRupiah = false) {
                      if (isRupiah) return new Intl.NumberFormat('id-ID', {
                          style: 'currency',
                          currency: 'IDR',
                          minimumFractionDigits: 0
                      }).format(value);
                      return new Intl.NumberFormat('id-ID').format(value);
                  }

                  const defaultOptions = {
                      responsive: true,
                      maintainAspectRatio: false,
                      plugins: {
                          legend: {
                              display: false
                          }
                      },
                      scales: {
                          x: {
                              ticks: {
                                  font: {
                                      size: 8
                                  }
                              }
                          },
                          y: {
                              beginAtZero: true
                          }
                      }
                  };

                  // --- Tanggal unik dari semua platform
                  const tanggalLabels = @json(array_values(array_unique(collect($omsetDataPerPlat)->flatten(1)->pluck('tanggal')->toArray())));

                  // --- Omset & CU per tanggal (gabung semua platform)
                  const omsetValues = tanggalLabels.map(d => {
                      return Object.values(@json($omsetDataPerPlat)).reduce((sum, platArr) => {
                          const found = platArr.find(r => r.tanggal === d);
                          return sum + (found ? parseFloat(found.total_omset) : 0);
                      }, 0);
                  });

                  const cuValues = tanggalLabels.map(d => {
                      return Object.values(@json($cuDataPerPlat)).reduce((sum, platArr) => {
                          const found = platArr.find(r => r.tanggal === d);
                          return sum + (found ? parseFloat(found.total_cu) : 0);
                      }, 0);
                  });

                  // --- Grafik Omset
                  new Chart(document.getElementById('grafikOmset'), {
                      type: 'bar',
                      data: {
                          labels: tanggalLabels,
                          datasets: [{
                              data: omsetValues,
                              backgroundColor: getColors(omsetValues, 'blue', 'red')
                          }]
                      },
                      options: defaultOptions
                  });

                  // --- Grafik CU
                  new Chart(document.getElementById('grafikCU'), {
                      type: 'bar',
                      data: {
                          labels: tanggalLabels,
                          datasets: [{
                              data: cuValues,
                              backgroundColor: getColors(cuValues, 'green', 'red')
                          }]
                      },
                      options: defaultOptions
                  });

                  // --- Transaksi per jam (gabung semua platform)
                  const jamLabels = [];
                  const jamData = [];
                  Object.values(@json($transaksiJamPerPlat)).forEach(platArr => {
                      platArr.forEach(j => {
                          const idx = jamLabels.indexOf(j.jam);
                          if (idx === -1) {
                              jamLabels.push(j.jam);
                              jamData.push(j.total);
                          } else {
                              jamData[idx] += j.total;
                          }
                      });
                  });

                  new Chart(document.getElementById('grafikTransaksi'), {
                      type: 'bar',
                      data: {
                          labels: jamLabels,
                          datasets: [{
                              data: jamData,
                              backgroundColor: getColors(jamData, 'orange', 'red')
                          }]
                      },
                      options: defaultOptions
                  });

                  // --- Pareto Top 10 (gabung semua platform)
                  let paretoTop = [];
                  Object.values(@json($paretoDataPerPlat)).forEach(platArr => {
                      platArr.forEach(p => {
                          const existing = paretoTop.find(e => e.produk === p.produk);
                          if (existing) existing.total += p.total;
                          else paretoTop.push({
                              produk: p.produk,
                              total: p.total
                          });
                      });
                  });
                  paretoTop = paretoTop.sort((a, b) => b.total - a.total).slice(0, 10);

                  new Chart(document.getElementById('grafikPareto'), {
                      type: 'bar',
                      data: {
                          labels: paretoTop.map(p => p.produk),
                          datasets: [{
                              data: paretoTop.map(p => p.total),
                              backgroundColor: 'purple'
                          }]
                      },
                      options: defaultOptions
                  });
              </script>
          @endif


          @include('Temp.footer')
