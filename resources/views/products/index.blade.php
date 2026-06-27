@extends('layouts.app')

@section('title', 'Products Directory - Product API Manager')
@section('active-products', 'active')

@section('content')
<div class="container-xl">
    <!-- Alert Message Placeholder -->
    <div id="toast-alert" class="alert alert-success alert-dismissible d-none mt-2" role="alert">
        <div class="d-flex">
            <div><i class="ti ti-circle-check alert-icon me-2"></i></div>
            <div id="toast-alert-message">Action completed successfully.</div>
        </div>
        <a class="btn-close" data-bs-dismiss="alert" aria-label="close"></a>
    </div>

    <div class="page-header d-print-none">
        <div class="row align-items-center">
            <div class="col">
                <h2 class="page-title text-dark">Products Directory</h2>
                <p class="text-secondary mt-1">Manage, search, and view specifications in the product inventory.</p>
            </div>
            <div class="col-auto ms-auto d-print-none">
                <a href="{{ route('products.create') }}" class="btn btn-primary">
                    <i class="ti ti-plus me-1"></i> Add New Product
                </a>
            </div>
        </div>
    </div>

    <!-- Products Table Card -->
    <div class="card card-animate mt-3">
        <div class="card-header py-3 bg-light">
            <div class="row g-2 align-items-center w-100">
                <div class="col">
                    <h3 class="card-title text-secondary">All Products</h3>
                </div>
                <div class="col-auto ms-auto">
                    <div class="input-icon">
                        <span class="input-icon-addon">
                            <i class="ti ti-search"></i>
                        </span>
                        <input type="text" id="search-input" class="form-control" placeholder="Search by name..." aria-label="Search products">
                    </div>
                </div>
            </div>
        </div>
        
        <div class="table-responsive" style="min-height: 250px;">
            <table class="table card-table table-vcenter text-nowrap table-hover">
                <thead>
                    <tr>
                        <th class="w-1">ID</th>
                        <th>Product Name</th>
                        <th>Description</th>
                        <th>Price</th>
                        <th>Quantity</th>
                        <th class="w-1 text-end">Actions</th>
                    </tr>
                </thead>
                <tbody id="products-tbody">
                    <tr class="table-loader-row">
                        <td colspan="6">
                            <div class="spinner-wrapper text-center">
                                <div class="spinner-border text-primary" role="status"></div>
                                <div class="text-secondary mt-2 small">Fetching catalog...</div>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Card Footer with Pagination -->
        <div class="card-footer d-flex align-items-center justify-content-between bg-light border-top">
            <p class="m-0 text-secondary small" id="pagination-summary">
                Showing 0 to 0 of 0 entries
            </p>
            <ul class="pagination pagination-sm m-0 ms-auto" id="pagination-links">
                <!-- Dynamic pagination links -->
            </ul>
        </div>
    </div>
</div>

<!-- View Product Detail Modal -->
<div class="modal modal-blur fade" id="view-modal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="ti ti-package me-2 text-primary"></i>Product Specifications</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="modal-loader" class="text-center py-4">
                    <div class="spinner-border text-primary" role="status"></div>
                    <p class="text-secondary mt-2 small">Fetching details...</p>
                </div>
                <div id="modal-details" class="row g-3 d-none">
                    <div class="col-12">
                        <label class="form-label text-secondary small">Product Name</label>
                        <div class="h3 text-dark fw-bold" id="view-product-name">...</div>
                    </div>
                    <div class="col-12">
                        <label class="form-label text-secondary small">Description</label>
                        <p class="text-dark bg-light p-3 rounded" id="view-product-description" style="white-space: pre-wrap;">...</p>
                    </div>
                    <div class="col-6">
                        <label class="form-label text-secondary small">Price</label>
                        <div class="h3 text-primary fw-bold" id="view-product-price">...</div>
                    </div>
                    <div class="col-6">
                        <label class="form-label text-secondary small">Stock Quantity</label>
                        <div class="h3 text-dark fw-semibold" id="view-product-quantity">...</div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary ms-auto" data-bs-dismiss="modal">Close</button>
                <a href="#" class="btn btn-primary" id="view-edit-link">Edit Product</a>
            </div>
        </div>
    </div>
</div>

<!-- Confirm Delete Modal (Tabler style) -->
<div class="modal modal-blur fade" id="delete-modal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-sm modal-dialog-centered" role="document">
        <div class="modal-content">
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            <div class="modal-status bg-danger"></div>
            <div class="modal-body text-center py-4">
                <i class="ti ti-alert-triangle fs-1 text-danger mb-3 d-block"></i>
                <h3>Are you sure?</h3>
                <div class="text-secondary">Do you really want to delete this product? This action cannot be undone.</div>
            </div>
            <div class="modal-footer">
                <div class="w-100">
                    <div class="row">
                        <div class="col">
                            <button type="button" class="btn btn-link link-secondary w-100" data-bs-dismiss="modal">Cancel</button>
                        </div>
                        <div class="col">
                            <button type="button" class="btn btn-danger w-100" id="confirm-delete-btn">Delete</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const productsTbody = document.getElementById('products-tbody');
        const searchInput = document.getElementById('search-input');
        const paginationSummary = document.getElementById('pagination-summary');
        const paginationLinks = document.getElementById('pagination-links');
        
        let productToDeleteId = null;
        let deleteModal = null;
        let viewModal = null;

        const deleteModalEl = document.getElementById('delete-modal');
        const viewModalEl = document.getElementById('view-modal');
        
        if (typeof bootstrap !== 'undefined') {
            deleteModal = new bootstrap.Modal(deleteModalEl);
            viewModal = new bootstrap.Modal(viewModalEl);
        }

        let currentPage = 1;
        let currentSearch = '';
        let searchTimeout = null;

        // Fetch products via API
        async function loadProducts(page = 1, search = '') {
            productsTbody.innerHTML = `
                <tr class="table-loader-row">
                    <td colspan="6">
                        <div class="spinner-wrapper text-center">
                            <div class="spinner-border text-primary" role="status"></div>
                            <div class="text-secondary mt-2 small">Fetching catalog...</div>
                        </div>
                    </td>
                </tr>
            `;

            try {
                const response = await apiRequest(`/products?page=${page}&search=${encodeURIComponent(search)}`);
                
                if (response && response.success) {
                    renderTable(response.data || []);
                    renderPagination(response.meta);
                } else {
                    showErrorRow('Error loading products. Invalid API response.');
                }
            } catch (error) {
                showErrorRow(error.message || 'Failed to connect to the server.');
            }
        }

        // Render table records helper
        function renderTable(products) {
            if (products.length === 0) {
                productsTbody.innerHTML = `
                    <tr>
                        <td colspan="6" class="text-center py-5 text-secondary">
                            <i class="ti ti-package-off fs-1 d-block mb-2 text-muted"></i>
                            No products found matching your search.
                        </td>
                    </tr>
                `;
                return;
            }

            productsTbody.innerHTML = '';
            products.forEach(product => {
                productsTbody.innerHTML += `
                    <tr>
                        <td><span class="text-secondary fw-semibold">#${product.id}</span></td>
                        <td>
                            <a href="#" class="view-details-link text-reset fw-semibold" data-id="${product.id}">
                                ${escapeHtml(product.name)}
                            </a>
                        </td>
                        <td class="text-secondary text-truncate" style="max-width: 250px;">
                            ${escapeHtml(product.description || 'No description provided')}
                        </td>
                        <td class="fw-semibold text-dark">$${parseFloat(product.price).toFixed(2)}</td>
                        <td>
                            <span class="badge ${product.quantity > 10 ? 'bg-blue-lt' : (product.quantity > 0 ? 'bg-warning-lt' : 'bg-danger-lt')}">
                                ${product.quantity} units
                            </span>
                        </td>
                        <td class="text-end">
                            <div class="btn-list flex-nowrap justify-content-end">
                                <button class="btn btn-white btn-sm view-btn" data-id="${product.id}" title="View details">
                                    <i class="ti ti-eye me-1"></i> View
                                </button>
                                <a href="/products/${product.id}/edit" class="btn btn-white btn-sm" title="Edit product">
                                    <i class="ti ti-edit me-1"></i> Edit
                                </a>
                                <button class="btn btn-outline-danger btn-sm delete-btn" data-id="${product.id}" title="Delete product">
                                    <i class="ti ti-trash me-1"></i> Delete
                                </button>
                            </div>
                        </td>
                    </tr>
                `;
            });

            // Wire up View detail button click events
            document.querySelectorAll('.view-btn, .view-details-link').forEach(btn => {
                btn.addEventListener('click', (e) => {
                    e.preventDefault();
                    const id = e.currentTarget.getAttribute('data-id');
                    showProductDetails(id);
                });
            });

            // Wire up Delete confirmation modal click events
            document.querySelectorAll('.delete-btn').forEach(btn => {
                btn.addEventListener('click', (e) => {
                    productToDeleteId = e.currentTarget.getAttribute('data-id');
                    if (deleteModal) {
                        deleteModal.show();
                    } else {
                        if (confirm('Are you sure you want to delete this product?')) {
                            deleteProduct(productToDeleteId);
                        }
                    }
                });
            });
        }

        // Render pagination links helper
        function renderPagination(meta) {
            if (!meta) {
                paginationSummary.textContent = 'Showing 0 to 0 of 0 entries';
                paginationLinks.innerHTML = '';
                return;
            }

            const { current_page, last_page, from, to, total } = meta;
            
            paginationSummary.textContent = total > 0 
                ? `Showing ${from} to ${to} of ${total} entries`
                : `Showing 0 to 0 of 0 entries`;

            let paginationHtml = '';

            // Previous button
            paginationHtml += `
                <li class="page-item ${current_page === 1 ? 'disabled' : ''}">
                    <a class="page-link" href="#" data-page="${current_page - 1}" tabindex="-1" aria-disabled="${current_page === 1}">
                        <i class="ti ti-chevron-left me-1"></i> Prev
                    </a>
                </li>
            `;

            // Page numbers
            const range = 2;
            for (let i = 1; i <= last_page; i++) {
                if (i === 1 || i === last_page || (i >= current_page - range && i <= current_page + range)) {
                    paginationHtml += `
                        <li class="page-item ${i === current_page ? 'active' : ''}">
                            <a class="page-link" href="#" data-page="${i}">${i}</a>
                        </li>
                    `;
                } else if (i === current_page - range - 1 || i === current_page + range + 1) {
                    paginationHtml += `<li class="page-item disabled"><span class="page-link">...</span></li>`;
                }
            }

            // Next button
            paginationHtml += `
                <li class="page-item ${current_page === last_page ? 'disabled' : ''}">
                    <a class="page-link" href="#" data-page="${current_page + 1}">
                        Next <i class="ti ti-chevron-right ms-1"></i>
                    </a>
                </li>
            `;

            paginationLinks.innerHTML = paginationHtml;

            paginationLinks.querySelectorAll('.page-link').forEach(link => {
                link.addEventListener('click', (e) => {
                    e.preventDefault();
                    const page = parseInt(e.currentTarget.getAttribute('data-page'));
                    if (page && page !== currentPage) {
                        currentPage = page;
                        loadProducts(currentPage, currentSearch);
                    }
                });
            });
        }

        // Fetch single product details to render inside details modal
        async function showProductDetails(id) {
            if (viewModal) viewModal.show();

            const modalLoader = document.getElementById('modal-loader');
            const modalDetails = document.getElementById('modal-details');
            const nameEl = document.getElementById('view-product-name');
            const descEl = document.getElementById('view-product-description');
            const priceEl = document.getElementById('view-product-price');
            const qtyEl = document.getElementById('view-product-quantity');
            const editLink = document.getElementById('view-edit-link');

            modalLoader.classList.remove('d-none');
            modalDetails.classList.add('d-none');

            try {
                const response = await apiRequest(`/products/${id}`);
                
                if (response && response.success && response.data) {
                    const product = response.data;
                    nameEl.textContent = product.name;
                    descEl.textContent = product.description || 'No description available for this product.';
                    priceEl.textContent = `$${parseFloat(product.price).toFixed(2)}`;
                    qtyEl.textContent = `${product.quantity} units`;
                    editLink.setAttribute('href', `/products/${product.id}/edit`);

                    modalLoader.classList.add('d-none');
                    modalDetails.classList.remove('d-none');
                } else {
                    throw new Error('Could not parse specifications.');
                }
            } catch (error) {
                modalLoader.classList.add('d-none');
                alert(error.message || 'Failed to fetch details.');
                if (viewModal) viewModal.hide();
            }
        }

        // Perform Product Deletion request
        async function deleteProduct(id) {
            try {
                const response = await apiRequest(`/products/${id}`, {
                    method: 'DELETE'
                });

                if (response && response.success) {
                    showNotification('Product was successfully deleted.', 'success');
                    loadProducts(currentPage, currentSearch);
                } else {
                    showNotification(response.message || 'Failed to delete product', 'danger');
                }
            } catch (error) {
                showNotification(error.message || 'An error occurred during deletion.', 'danger');
            }
        }

        // Confirm delete action wire-up
        document.getElementById('confirm-delete-btn').addEventListener('click', () => {
            if (productToDeleteId) {
                deleteProduct(productToDeleteId);
                if (deleteModal) deleteModal.hide();
                productToDeleteId = null;
            }
        });

        // Display error row inside table helper
        function showErrorRow(message) {
            productsTbody.innerHTML = `
                <tr>
                    <td colspan="6" class="text-center py-5 text-danger fw-semibold">
                        <i class="ti ti-alert-circle fs-1 d-block mb-2"></i>
                        ${escapeHtml(message)}
                    </td>
                </tr>
            `;
            paginationSummary.textContent = 'Showing 0 to 0 of 0 entries';
            paginationLinks.innerHTML = '';
        }

        // Notification alert helper
        function showNotification(message, type = 'success') {
            const toast = document.getElementById('toast-alert');
            const toastMsg = document.getElementById('toast-alert-message');
            
            toast.className = `alert alert-${type} alert-dismissible mt-3`;
            const icon = toast.querySelector('.alert-icon');
            if (icon) {
                icon.className = `ti ti-${type === 'success' ? 'circle-check' : 'alert-circle'} alert-icon me-2`;
            }
            
            toastMsg.textContent = message;
            toast.classList.remove('d-none');

            setTimeout(() => {
                toast.classList.add('d-none');
            }, 4000);
        }

        // Search Debounce handler
        searchInput.addEventListener('input', (e) => {
            clearTimeout(searchTimeout);
            const query = e.target.value.trim();
            searchTimeout = setTimeout(() => {
                currentSearch = query;
                currentPage = 1;
                loadProducts(currentPage, currentSearch);
            }, 300);
        });

        // Helper to prevent XSS
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

        // Check redirect success message triggers
        const params = new URLSearchParams(window.location.search);
        if (params.get('msg')) {
            showNotification(params.get('msg'), 'success');
            window.history.replaceState({}, document.title, window.location.pathname);
        }

        loadProducts(currentPage, currentSearch);
    });
</script>
@endsection
