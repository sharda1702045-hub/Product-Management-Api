@extends('layouts.app')

@section('title', 'Dashboard - Product API Manager')
@section('active-dashboard', 'active')

@section('content')
<div class="container-xl">
    <div class="page-header d-print-none">
        <div class="row align-items-center">
            <div class="col">
                <h2 class="page-title text-dark">
                    Dashboard Overview
                </h2>
                <p class="text-secondary mt-1">Welcome back, <span class="user-name-placeholder fw-semibold text-primary">User</span>! Here is your product catalog status.</p>
            </div>
        </div>
    </div>
    
    <!-- Stats Grid -->
    <div class="row row-cards mt-2">
        <!-- Total Products Stat Card -->
        <div class="col-sm-6 col-lg-4">
            <div class="card card-sm card-animate border-start border-primary border-3">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-auto">
                            <span class="bg-primary text-white avatar">
                                <i class="ti ti-package fs-2"></i>
                            </span>
                        </div>
                        <div class="col">
                            <div class="font-weight-medium text-secondary">Total Products</div>
                            <div class="h1 mb-0 fw-bold" id="total-products-count">...</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Dummy Total Users Card -->
        <div class="col-sm-6 col-lg-4">
            <div class="card card-sm card-animate border-start border-success border-3">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-auto">
                            <span class="bg-success text-white avatar">
                                <i class="ti ti-users fs-2"></i>
                            </span>
                        </div>
                        <div class="col">
                            <div class="font-weight-medium text-secondary">Registered Users</div>
                            <div class="h1 mb-0 fw-bold">12 <span class="badge bg-green-lt fs-6 ms-2 align-middle">Active</span></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- System Status Card -->
        <div class="col-sm-6 col-lg-4">
            <div class="card card-sm card-animate border-start border-info border-3">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-auto">
                            <span class="bg-info text-white avatar" id="status-avatar">
                                <i class="ti ti-cloud-computing fs-2"></i>
                            </span>
                        </div>
                        <div class="col">
                            <div class="font-weight-medium text-secondary">API Service</div>
                            <div class="h1 mb-0 fw-bold" id="api-status-text">
                                <span class="spinner-border spinner-border-sm text-secondary" role="status"></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts & Recent Section -->
    <div class="row mt-4 g-4">
        <!-- Chart Column -->
        <div class="col-lg-6">
            <div class="card card-animate" style="height: 100%;">
                <div class="card-header bg-light">
                    <h3 class="card-title"><i class="ti ti-chart-bar me-2 text-primary"></i>Stock Quantities Visualizer</h3>
                </div>
                <div class="card-body d-flex flex-column justify-content-center align-items-center" style="min-height: 250px;">
                    <div id="chart-loader" class="spinner-border text-primary" role="status"></div>
                    <div id="chart-container" class="w-100 d-none">
                        <div id="stock-chart"></div>
                    </div>
                    <div id="chart-no-data" class="text-secondary d-none text-center">
                        <i class="ti ti-chart-pie-off fs-1 mb-2 text-muted"></i>
                        <p class="mb-0">No product stock data available to visualize.</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Products Column -->
        <div class="col-lg-6">
            <div class="card card-animate" style="height: 100%;">
                <div class="card-header d-flex align-items-center justify-content-between bg-light">
                    <h3 class="card-title"><i class="ti ti-history me-2 text-primary"></i>Recently Added</h3>
                    <a href="{{ route('products.index') }}" class="btn btn-outline-primary btn-sm">View All</a>
                </div>
                <div class="table-responsive" style="min-height: 250px;">
                    <table class="table card-table table-vcenter text-nowrap datatable">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Name</th>
                                <th>Price</th>
                                <th>Qty</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody id="recent-products-tbody">
                            <tr class="table-loader-row">
                                <td colspan="5">
                                    <div class="spinner-wrapper text-center">
                                        <div class="spinner-border text-primary" role="status"></div>
                                        <div class="text-secondary mt-2 small">Loading products...</div>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<!-- ApexCharts CDN -->
<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
<script>
    document.addEventListener('DOMContentLoaded', async () => {
        const totalProductsCountEl = document.getElementById('total-products-count');
        const apiStatusTextEl = document.getElementById('api-status-text');
        const statusAvatarEl = document.getElementById('status-avatar');
        const recentProductsTbody = document.getElementById('recent-products-tbody');
        
        const chartLoader = document.getElementById('chart-loader');
        const chartContainer = document.getElementById('chart-container');
        const chartNoData = document.getElementById('chart-no-data');

        try {
            const response = await apiRequest('/products');
            
            if (response && response.success) {
                // Update API Status badge
                apiStatusTextEl.innerHTML = '<span class="text-success">Online</span>';
                statusAvatarEl.className = "bg-success text-white avatar";

                // Update Total Products Count
                const total = (response.meta && typeof response.meta.total !== 'undefined') 
                    ? response.meta.total 
                    : (response.data ? response.data.length : 0);
                
                totalProductsCountEl.textContent = total;

                const productsList = response.data || [];
                
                if (productsList.length === 0) {
                    recentProductsTbody.innerHTML = `
                        <tr>
                            <td colspan="5" class="text-center py-4 text-secondary">
                                <i class="ti ti-package-off fs-1 d-block mb-2 text-muted"></i>
                                No products found. <a href="{{ route('products.create') }}">Add one!</a>
                            </td>
                        </tr>
                    `;
                    chartLoader.classList.add('d-none');
                    chartNoData.classList.remove('d-none');
                } else {
                    // Render Recent Products (max 5)
                    recentProductsTbody.innerHTML = '';
                    const recent = productsList.slice(0, 5);
                    recent.forEach(product => {
                        const date = new Date(product.created_at).toLocaleDateString(undefined, {
                            year: 'numeric',
                            month: 'short',
                            day: 'numeric'
                        });
                        recentProductsTbody.innerHTML += `
                            <tr>
                                <td><span class="text-secondary">#${product.id}</span></td>
                                <td><a href="/products/${product.id}/edit" class="text-reset fw-semibold">${escapeHtml(product.name)}</a></td>
                                <td class="fw-semibold text-dark">$${parseFloat(product.price).toFixed(2)}</td>
                                <td>
                                    <span class="badge ${product.quantity > 5 ? 'bg-blue-lt' : (product.quantity > 0 ? 'bg-warning-lt' : 'bg-danger-lt')}">
                                        ${product.quantity}
                                    </span>
                                </td>
                                <td class="text-secondary">${date}</td>
                            </tr>
                        `;
                    });

                    // Render ApexCharts Horizontal Bar chart for stock quantities
                    chartLoader.classList.add('d-none');
                    chartContainer.classList.remove('d-none');

                    // Take top 6 products or all if less than 6
                    const chartProducts = productsList.slice(0, 6);
                    const names = chartProducts.map(p => p.name.length > 20 ? p.name.substring(0, 20) + '...' : p.name);
                    const quantities = chartProducts.map(p => p.quantity);

                    const options = {
                        series: [{
                            name: 'Stock Quantity',
                            data: quantities
                        }],
                        chart: {
                            type: 'bar',
                            height: 260,
                            toolbar: { show: false }
                        },
                        colors: ['#206bc4'],
                        plotOptions: {
                            bar: {
                                borderRadius: 4,
                                horizontal: true,
                            }
                        },
                        dataLabels: {
                            enabled: true,
                            style: {
                                colors: ['#fff']
                            }
                        },
                        xaxis: {
                            categories: names,
                        },
                        tooltip: {
                            theme: document.documentElement.getAttribute('data-bs-theme') || 'light'
                        }
                    };

                    const chart = new ApexCharts(document.querySelector("#stock-chart"), options);
                    chart.render();
                }
            } else {
                throw new Error('Invalid API response');
            }
        } catch (error) {
            // Update UI on Connection/API Error
            totalProductsCountEl.textContent = 'N/A';
            apiStatusTextEl.innerHTML = '<span class="text-danger">Offline</span>';
            statusAvatarEl.className = "bg-danger text-white avatar";
            chartLoader.classList.add('d-none');
            chartNoData.classList.remove('d-none');
            
            recentProductsTbody.innerHTML = `
                <tr>
                    <td colspan="5" class="text-center py-4 text-danger">
                        <i class="ti ti-wifi-off fs-1 d-block mb-2"></i>
                        Failed to connect to the Product API. Ensure backend is running.
                    </td>
                </tr>
            `;
        }

        // Simple HTML escape function to prevent XSS
        function escapeHtml(string) {
            return String(string).replace(/[&<>"']/g, function (s) {
                return {
                    '&': '&amp;',
                    '<': '&lt;',
                    '>': '&gt;',
                    '"': '&quot;',
                    "'": '&#39;'
                }[s];
            });
        }
    });
</script>
@endsection
