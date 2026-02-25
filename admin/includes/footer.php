        </div>
    </main>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
    // Confirm delete
    function confirmDelete(message = 'Are you sure you want to delete this item?') {
        return confirm(message);
    }
    
    // Image preview
    function previewImage(input, previewId) {
        const preview = document.getElementById(previewId);
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                preview.src = e.target.result;
                preview.style.display = 'block';
            };
            reader.readAsDataURL(input.files[0]);
        }
    }
    
    // Add more images
    document.getElementById('add-more-images')?.addEventListener('click', function() {
        const container = document.getElementById('additional-images');
        const div = document.createElement('div');
        div.className = 'mb-2';
        div.innerHTML = `
            <input type="file" name="additional_images[]" class="form-control" accept="image/*">
        `;
        container.appendChild(div);
    });
    
    // Add color
    document.getElementById('add-color')?.addEventListener('click', function() {
        const container = document.getElementById('colors-container');
        const div = document.createElement('div');
        div.className = 'row g-2 mb-2';
        div.innerHTML = `
            <div class="col-5">
                <input type="text" name="color_name[]" class="form-control" placeholder="Color Name">
            </div>
            <div class="col-5">
                <input type="color" name="color_code[]" class="form-control" style="height: 38px;">
            </div>
            <div class="col-2">
                <button type="button" class="btn btn-outline-danger w-100" onclick="this.parentElement.parentElement.remove()">
                    <i class="bi bi-trash"></i>
                </button>
            </div>
        `;
        container.appendChild(div);
    });
    
    // Add variant
    document.getElementById('add-variant')?.addEventListener('click', function() {
        const container = document.getElementById('variants-container');
        const div = document.createElement('div');
        div.className = 'row g-2 mb-2';
        div.innerHTML = `
            <div class="col-4">
                <input type="text" name="variant_name[]" class="form-control" placeholder="Variant Name">
            </div>
            <div class="col-3">
                <input type="number" name="variant_price[]" class="form-control" placeholder="Price Adj" step="0.01">
            </div>
            <div class="col-3">
                <input type="number" name="variant_stock[]" class="form-control" placeholder="Stock">
            </div>
            <div class="col-2">
                <button type="button" class="btn btn-outline-danger w-100" onclick="this.parentElement.parentElement.remove()">
                    <i class="bi bi-trash"></i>
                </button>
            </div>
        `;
        container.appendChild(div);
    });
    </script>
</body>
</html>
