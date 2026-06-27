@extends('layouts.app')

@section('title', 'Add Product - Product API Manager')
@section('active-create-product', 'active')

@section('content')
<div class="container-xl">
    <div class="page-header d-print-none">
        <div class="row align-items-center">
            <div class="col">
                <h2 class="page-title text-dark">Add New Product</h2>
                <p class="text-secondary mt-1">Insert a new item details to add it to the inventory database.</p>
            </div>
            <div class="col-auto ms-auto">
                <a href="{{ route('products.index') }}" class="btn btn-outline-secondary">
                    <i class="ti ti-arrow-left me-1"></i> Back to Catalog
                </a>
            </div>
        </div>
    </div>

    <!-- Add Product Form Card -->
    <div class="card card-animate mt-3">
        <div class="card-header bg-light">
            <h3 class="card-title text-secondary"><i class="ti ti-package-import me-2 text-primary"></i>Product Details</h3>
        </div>
        
        <!-- Alert Banners inside form -->
        <div class="px-4 pt-3">
            <div id="alert-container"></div>
        </div>

        <form id="create-product-form" class="card-body" novalidate>
            <div class="row">
                <!-- Name field -->
                <div class="col-12 mb-3">
                    <label class="form-label required">Product Name</label>
                    <input type="text" id="name" class="form-control" placeholder="e.g. Wireless Noise Canceling Headphones" required>
                    <div class="invalid-feedback" id="name-error">Please provide a product name.</div>
                </div>

                <!-- Description field -->
                <div class="col-12 mb-3">
                    <label class="form-label">Product Description</label>
                    <textarea id="description" class="form-control" rows="4" placeholder="Brief details about product dimensions, warranty, colors, specifications..."></textarea>
                    <div class="invalid-feedback" id="description-error">Description is invalid.</div>
                </div>

                <!-- Price field -->
                <div class="col-md-6 mb-3">
                    <label class="form-label required">Price (USD)</label>
                    <div class="input-group">
                        <span class="input-group-text">$</span>
                        <input type="number" id="price" class="form-control" step="0.01" min="0.01" placeholder="29.99" required>
                        <div class="invalid-feedback" id="price-error">Please set a price greater than 0.01.</div>
                    </div>
                </div>

                <!-- Quantity field -->
                <div class="col-md-6 mb-3">
                    <label class="form-label required">Quantity in Stock</label>
                    <input type="number" id="quantity" class="form-control" min="0" placeholder="50" required>
                    <div class="invalid-feedback" id="quantity-error">Please enter a valid stock count (0 or more).</div>
                </div>
            </div>

            <div class="form-footer border-top pt-3 mt-2 d-flex justify-content-end gap-2">
                <a href="{{ route('products.index') }}" class="btn btn-link link-secondary">Cancel</a>
                <button type="submit" id="submit-btn" class="btn btn-primary">
                    <span class="spinner-border spinner-border-sm me-2 d-none" role="status" id="submit-spinner"></span>
                    Save Product
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const form = document.getElementById('create-product-form');
        const nameInput = document.getElementById('name');
        const descInput = document.getElementById('description');
        const priceInput = document.getElementById('price');
        const qtyInput = document.getElementById('quantity');
        
        const submitBtn = document.getElementById('submit-btn');
        const submitSpinner = document.getElementById('submit-spinner');
        const alertContainer = document.getElementById('alert-container');

        function showAlert(message, type = 'danger') {
            alertContainer.innerHTML = `
                <div class="alert alert-${type} alert-dismissible" role="alert">
                    <div class="d-flex">
                        <div><i class="ti ti-${type === 'success' ? 'circle-check' : 'alert-circle'} alert-icon me-2"></i></div>
                        <div>${message}</div>
                    </div>
                    <a class="btn-close" data-bs-dismiss="alert" aria-label="close"></a>
                </div>
            `;
        }

        form.addEventListener('submit', async (e) => {
            e.preventDefault();

            // Clear errors and validation classes
            alertContainer.innerHTML = '';
            nameInput.classList.remove('is-invalid');
            descInput.classList.remove('is-invalid');
            priceInput.classList.remove('is-invalid');
            qtyInput.classList.remove('is-invalid');

            let hasError = false;

            // Simple Client Validation
            if (!nameInput.value.trim()) {
                nameInput.classList.add('is-invalid');
                hasError = true;
            }

            if (!priceInput.value || parseFloat(priceInput.value) < 0.01) {
                priceInput.classList.add('is-invalid');
                hasError = true;
            }

            if (qtyInput.value === '' || parseInt(qtyInput.value) < 0) {
                qtyInput.classList.add('is-invalid');
                hasError = true;
            }

            if (hasError) return;

            // Toggle loading spinner
            submitBtn.disabled = true;
            submitSpinner.classList.remove('d-none');

            try {
                const response = await apiRequest('/products', {
                    method: 'POST',
                    body: {
                        name: nameInput.value.trim(),
                        description: descInput.value.trim() || null,
                        price: parseFloat(priceInput.value),
                        quantity: parseInt(qtyInput.value)
                    }
                });

                if (response && response.success) {
                    showAlert('Product created successfully! Redirecting...', 'success');
                    
                    setTimeout(() => {
                        window.location.href = `/products?msg=${encodeURIComponent('Product was successfully created.')}`;
                    }, 1000);
                } else {
                    showAlert(response.message || 'Failed to create product', 'danger');
                    submitBtn.disabled = false;
                    submitSpinner.classList.add('d-none');
                }
            } catch (error) {
                submitBtn.disabled = false;
                submitSpinner.classList.add('d-none');

                if (error.data && error.data.data) {
                    // Display Laravel's validation messages
                    const validationErrors = error.data.data;
                    if (validationErrors.name) {
                        nameInput.classList.add('is-invalid');
                        document.getElementById('name-error').textContent = validationErrors.name.join(' ');
                    }
                    if (validationErrors.description) {
                        descInput.classList.add('is-invalid');
                        document.getElementById('description-error').textContent = validationErrors.description.join(' ');
                    }
                    if (validationErrors.price) {
                        priceInput.classList.add('is-invalid');
                        document.getElementById('price-error').textContent = validationErrors.price.join(' ');
                    }
                    if (validationErrors.quantity) {
                        qtyInput.classList.add('is-invalid');
                        document.getElementById('quantity-error').textContent = validationErrors.quantity.join(' ');
                    }
                    showAlert('Please fix the validation errors.', 'danger');
                } else {
                    showAlert(error.message || 'An error occurred. Please try again.', 'danger');
                }
            }
        });
    });
</script>
@endsection
