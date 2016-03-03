            <!-- page content -->
            <div class="right_col" role="main">
                <div class="">
                    <div class="clearfix"></div>
                    <div class="row">
                        <div class="col-md-12 col-sm-12 col-xs-12">
                            <div class="x_panel">
                                <div class="x_title">
                                    <h2>Tambah Pulsa</h2>
                                    <div class="clearfix"></div>
                                </div>
                                <div class="x_content">
                                    <br />
                                    <form class="form-horizontal form-label-left" method="post" action="<?php echo site_url("admin/add_pulsa"); ?>">

                                        <div class="form-group">
                                            <label class="control-label col-md-3 col-sm-3 col-xs-12" for="provider">Provider <span class="required">*</span>
                                            </label>
                                            <div class="col-md-3 col-sm-3 col-xs-12">
                                                <select class="form-control" name="provider">
                                                    <?php foreach ($operators as $value) { ?>
                                                        <option value="<?php echo $value->operator_id; ?>"><?php echo $value->nama; ?></option>
                                                    <?php } ?>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label class="control-label col-md-3 col-sm-3 col-xs-12" for="tipe_pembelian">Tipe Pembelian <span class="required">*</span>
                                            </label>
                                            <div class="col-md-3 col-sm-3 col-xs-12">
                                                <input type="text" name="tipe_pembelian" required="required" class="form-control col-md-7 col-xs-12">
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label for="nama" class="control-label col-md-3 col-sm-3 col-xs-12">Nama <span class="required">*</label>
                                            <div class="col-md-4 col-sm-4 col-xs-12">
                                                <input class="form-control col-md-7 col-xs-12" type="text" name="nama" required="required">
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label class="control-label col-md-3 col-sm-3 col-xs-12" for="harga">Harga <span class="required">*</span>
                                            </label>
                                            <div class="col-md-2 col-sm-2 col-xs-12">
                                                <input class="form-control col-md-7 col-xs-12" required="required" type="text" name="harga">
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label class="control-label col-md-3 col-sm-3 col-xs-12" for="kode_sms">Kode SMS <span class="required">*</span>
                                            </label>
                                            <div class="col-md-2 col-sm-2 col-xs-12">
                                                <input class="form-control col-md-7 col-xs-12" required="required" type="text" name="kode_sms">
                                            </div>
                                        </div>
                                        <div class="ln_solid"></div>
                                        <div class="form-group">
                                            <div class="col-md-6 col-sm-6 col-xs-12 col-md-offset-3">
                                                <button type="submit" class="btn btn-success">Tambahkan</button>
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