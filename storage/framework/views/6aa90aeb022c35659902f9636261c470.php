 <!-- Or your base layout -->

<?php $__env->startSection('content'); ?>
<div class="container">
<div class="container mx-auto text-center my-4">
    <h2 class="text-2xl font-semibold">Supplier Form</h2>
</div>

    <?php if(session('success')): ?>
        <div class="alert alert-success">
            <?php echo e(session('success')); ?>

        </div>
    <?php endif; ?>
    
    <?php if($errors->any()): ?>
        <div class="alert alert-danger">
            <ul>
                <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <li><?php echo e($error); ?></li>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </ul>
        </div>
    <?php endif; ?>
    <form action="<?php echo e(route('vendor.store')); ?>" method="POST" enctype="multipart/form-data">
        <?php echo csrf_field(); ?>
        
        <div class="row">
            <!-- Contact Details Section -->
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h3>Contact Details</h3>
                    </div>
                    <div class="card-body">
                       
                        
                        <div class="form-group">
                            <label for="name">Name</label>
                            <input type="text" class="form-control" id="name" name="name" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="address">Address</label>
                            <textarea class="form-control" id="address" name="address" required></textarea>
                        </div>
                        
                        <div class="form-group">
                            <label for="contact_person">Contact Person</label>
                            <input type="text" class="form-control" id="contact_person" name="contact_person" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="contact_person_phone">Contact Person Phone Number</label>
                            <input type="tel" class="form-control" id="contact_person_phone" name="contact_person_phone" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="email">Email</label>
                            <input type="email" class="form-control" id="email" name="email" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="company_website">Company Website</label>
                            <input type="url" class="form-control" id="company_website" name="company_website">
                        </div>
                        
                        <div class="form-group">
                            <label for="experience">Experience</label>
                            <input type="text" class="form-control" id="experience" name="experience">
                        </div>
                        
                        <div class="form-group">
                            <label for="plan_location">Plan - Location</label>
                            <input type="text" class="form-control" id="plan_location" name="plan_location">
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Product Details Section -->
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h3>Product Details</h3>
                    </div>
                    <div class="card-body">
                        <div class="form-group">
                            <label for="category_id">Category</label>
                            <select class="form-control" id="category_id" name="category_id" required>
                                <option value="">Select Category</option>
                                <?php $__currentLoopData = $categories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $category): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($category->id); ?>"><?php echo e($category->name); ?></option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="sub_category_id">Sub Category</label>
                            <select class="form-control" id="sub_category_id" name="sub_category_id" disabled required>
                                <option value="">Select Sub Category</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="product">Product</label>
                            <input type="text" class="form-control" id="product" name="product" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="product_image">Product Image</label>
                            <input type="file" class="form-control" id="product_image" name="product_image">
                        </div>
                        
                        <div class="form-group">
                            <label for="area_of_application">Area of application</label>
                            <textarea class="form-control" id="area_of_application" name="area_of_application"></textarea>
                        </div>
                        
                        <div class="form-group">
                            <label for="bag_description">Bag description</label>
                            <textarea class="form-control" id="bag_description" name="bag_description"></textarea>
                        </div>
                        
                        <div class="form-group">
                            <label for="rate_in_pure">Rate in Pure £</label>
                            <input type="number" step="0.01" class="form-control" id="rate_in_pure" name="rate_in_pure" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="for_supply_rate">For supply rate</label>
                            <input type="number" step="0.01" class="form-control" id="for_supply_rate" name="for_supply_rate" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="for_apply_rate">For apply rate</label>
                            <input type="number" step="0.01" class="form-control" id="for_apply_rate" name="for_apply_rate" required>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="form-group mt-3">
            <button type="submit" class="btn btn-primary">Submit</button>
        </div>
    </form>
</div>


<!-- AJAX script for dynamic subcategories -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    $(document).ready(function() {
        $('#category_id').change(function() {
            var categoryId = $(this).val();
            
            if (categoryId) {
                $('#sub_category_id').prop('disabled', false);
                
                $.ajax({
                    url: "<?php echo e(route('get.subcategories', '')); ?>/" + categoryId,
                    type: 'GET',
                    dataType: 'json',
                    success: function(data) {
                        $('#sub_category_id').empty();
                        $('#sub_category_id').append('<option value="">Select Sub Category</option>');
                        
                        $.each(data, function(key, value) {
                            $('#sub_category_id').append('<option value="' + key + '">' + value + '</option>');
                        });
                    }
                });
            } else {
                $('#sub_category_id').prop('disabled', true);
                $('#sub_category_id').empty();
                $('#sub_category_id').append('<option value="">Select Sub Category</option>');
            }
        });
    });
</script>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.minimal', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\xampp\htdocs\hrm_archivista\Modules/LandingPage\Resources/views/layouts/vendor.blade.php ENDPATH**/ ?>