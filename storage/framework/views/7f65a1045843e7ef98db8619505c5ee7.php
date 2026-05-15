<?php $__env->startSection('page-title'); ?>
    <?php echo e(__('Offer Letter')); ?>

<?php $__env->stopSection(); ?>

<?php $__env->startPush('css-page'); ?>
    <style>
        #boxes.offer-archivista {
            font-family: 'Times New Roman', Times, serif;
            font-size: 11.5pt;
            line-height: 1.35;
            color: #000;
            max-width: 21cm;
            margin: 0 auto;
            padding: 1.35cm 1.2cm 0.5cm 1.18cm;
            box-sizing: border-box;
            background: #fff;
        }

        #boxes.offer-archivista p {
            margin: 0 0 0.35em 0;
        }

        #boxes.offer-archivista .ol-archivista {
            margin: 0.35em 0 0.35em 0;
            padding-left: 2.2em;
        }

        #boxes.offer-archivista .ol-archivista li {
            margin-bottom: 0.35em;
            text-align: justify;
        }

        #boxes.offer-archivista .title-offer {
            text-align: center;
            font-weight: bold;
            font-size: 11.5pt;
            text-decoration: underline;
            margin: 0.6em 0 1.2em 0;
        }

        #boxes.offer-archivista .date-line {
            text-align: center;
            margin-bottom: 0.5em;
        }

        #boxes.offer-archivista .sub-line {
            margin-top: 1em;
        }

        #boxes.offer-archivista .justify {
            text-align: justify;
        }

        #boxes.offer-archivista .sign-block {
            margin-top: 1.25em;
            font-weight: bold;
        }

        #boxes.offer-archivista .received {
            text-align: right;
            margin-top: 2.2em;
        }
    </style>
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>
    <div class="row">
        <div class="col-12 d-flex justify-content-center">
            <div class="card border-0 shadow-none bg-transparent">
                <div class="card-body p-0">
                    <div id="boxes" class="offer-archivista">
                        <p class="date-line"><strong>Date:</strong> <?php echo e($joiningFormatted); ?></p>

                        <p class="title-offer"><?php echo e(__('Offer Letter')); ?>.</p>

                        <p class="sub-line"><strong><?php echo e(__('Sub')); ?>:</strong>&nbsp;&nbsp;&nbsp;&nbsp;<strong><u><?php echo e(__('Offer Letter')); ?></u></strong></p>

                        <p style="margin-top: 1.1em; font-weight: bold; font-size: 11.5pt;">
                            <?php echo e(__('Dear')); ?> <?php echo e($namePrefix); ?><br><?php echo e($employees->name); ?>

                        </p>

                        <p class="justify" style="margin-top: 0.9em;">
                            <?php echo e(__('With reference to your application and subsequent interview held in our office, we are pleased to offer you the job for the post of')); ?>

                            &nbsp;<strong>“<?php echo e($departmentName); ?>”</strong>&nbsp;<?php echo e(__('in our company.')); ?>

                        </p>

                        <p class="justify" style="margin-top: 1.1em;">
                            <?php echo e(__('We are pleased to confirm the offer of employment for the above position on terms and conditions mutually discussed and agreed. The detailed appointment letter will be given to you at the time of joining. You have to join on or before')); ?>

                            &nbsp;<strong><?php echo e($joiningFormatted); ?></strong>&nbsp;<?php echo e(__('otherwise this offer will stand withdrawn automatically.')); ?>

                        </p>

                        <p class="justify" style="margin-top: 1.1em;">
                            <?php echo e(__('You are requested to bring attested copies along with the original certificates/ testimonials at the time of joining the following:')); ?>

                        </p>

                        <ol class="ol-archivista justify">
                            <li><?php echo e(__('Educational certificates.')); ?></li>
                            <li><?php echo e(__('Experience certificates, Copy of resignation / acceptance letter and relieving letter from previous employer.')); ?></li>
                            <li><?php echo e(__('Salary Slip / latest salary structure from ex-employer.')); ?></li>
                            <li><?php echo e(__('Three pass port Size photographs.')); ?></li>
                            <li><?php echo e(__('Passport (If available.)')); ?></li>
                            <li><?php echo e(__('Medical fitness certificate.')); ?></li>
                            <li><?php echo e(__('Copy of PAN Card.')); ?></li>
                        </ol>

                        <p class="justify" style="margin-top: 0.6em;">
                            <?php echo e(__('Please return the enclosed copy duly signed as a token of your acceptance of the letter.')); ?>

                        </p>

                        <p class="sign-block">
                            <?php echo e(__('For')); ?> <?php echo e($companyName); ?>

                        </p>

                        <p style="margin-top: 2.2em; font-weight: bold;">
                            <?php echo e($signatoryName); ?><br><?php echo e($signatoryTitle); ?>

                        </p>

                        <p class="received">
                            <?php echo e(__('Received')); ?> &amp; <?php echo e(__('Accepted')); ?><span style="border-bottom: 1px solid #000; display: inline-block; min-width: 10em; margin-left: 0.35em;"></span>
                        </p>
                        <p class="received" style="margin-top: 0.35em;">
                            (<?php echo e(__('Signature')); ?> &amp; <?php echo e(__('Date')); ?>)
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('script-page'); ?>
    <script type="text/javascript" src="<?php echo e(asset('js/html2pdf.bundle.min.js')); ?>"></script>
    <script>
        function closeScript() {
            setTimeout(function() {
                window.open(window.location, '_self').close();
            }, 1000);
        }

        $(window).on('load', function() {
            var element = document.getElementById('boxes');
            var opt = {
                filename: <?php echo json_encode($name->name); ?>,
                image: {
                    type: 'jpeg',
                    quality: 1
                },
                html2canvas: {
                    scale: 4,
                    dpi: 72,
                    letterRendering: true
                },
                jsPDF: {
                    unit: 'in',
                    format: 'a4'
                }
            };

            html2pdf().set(opt).from(element).save().then(closeScript);
        });
    </script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.contractheader', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\xampp\htdocs\hrm_archivista\resources\views/employee/template/offer_letter_archivista.blade.php ENDPATH**/ ?>