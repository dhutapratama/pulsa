            <!-- page content -->
            <div class="right_col" role="main">
                <div class="">
                    <div class="clearfix"></div>
                    <div class="row">
                        <div class="col-md-12 col-sm-12 col-xs-12">
                            <div class="x_panel">
                                <div class="x_title">
                                    <h2>Ubah Password Administrator</h2>
                                    <div class="clearfix"></div>
                                </div>
                                <div class="x_content">
                                    <br />
                                    <?php if (isset($alert)) { ?>
                                        <div class="alert alert-success alert-dismissible fade in" role="alert">
                                            <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">×</span>
                                            </button>
                                            <?php echo $alert; ?>
                                        </div>
                                    <?php } ?>
                                    
                                    <form class="form-horizontal form-label-left" method="post" action="<?php echo site_url("admin/change_password"); ?>">
                                        <div class="form-group">
                                            <label for="nama" class="control-label col-md-3 col-sm-3 col-xs-12">Password Baru <span class="required">*</label>
                                            <div class="col-md-4 col-sm-4 col-xs-12">
                                                <input class="form-control col-md-7 col-xs-12" type="password" name="password" required="required">
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label for="nama" class="control-label col-md-3 col-sm-3 col-xs-12">Ulangi Password <span class="required">*</label>
                                            <div class="col-md-4 col-sm-4 col-xs-12">
                                                <input class="form-control col-md-7 col-xs-12" type="password" name="repassword" required="required">
                                            </div>
                                        </div>
                                        <div class="ln_solid"></div>
                                        <div class="form-group">
                                            <div class="col-md-6 col-sm-6 col-xs-12 col-md-offset-3">
                                                <button type="submit" class="btn btn-success">Simpan</button>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
                <!-- /page content -->
            </div>

        </div>
    </div>

        <div id="custom_notifications" class="custom-notifications dsp_none">
            <ul class="list-unstyled notifications clearfix" data-tabbed_notifications="notif-group">
            </ul>
            <div class="clearfix"></div>
            <div id="notif-group" class="tabbed_notifications"></div>
        </div>

        <script src="/js/bootstrap.min.js"></script>

        <!-- chart js -->
        <script src="/js/chartjs/chart.min.js"></script>
        <!-- bootstrap progress js -->
        <script src="/js/progressbar/bootstrap-progressbar.min.js"></script>
        <script src="/js/nicescroll/jquery.nicescroll.min.js"></script>\
        <script src="/js/pace/pace.min.js"></script>
        <script src="/js/custom.js"></script>
</body>

</html>