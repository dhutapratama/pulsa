           <!-- page content -->
            <div class="right_col" role="main">
                <div class="">
                    <div class="row">

                        <div class="col-md-12 col-sm-12 col-xs-12">
                            <div class="x_panel">
                                <div class="x_title">
                                    <h2>Daftar Harga Pulsa</h2>
                                    <div class="clearfix"></div>
                                </div>
                                <div class="x_content">
                                    <table id="example" class="table table-striped responsive-utilities jambo_table">
                                        <thead>
                                            <tr class="headings">
                                                <th>Provider</th>
                                                <th>Tipe Pembelian</th>
                                                <th>Nama</th>
                                                <th>Harga</th>
                                                <th>Kode SMS</th>
                                                <th class=" no-link last"><span class="nobr">Opsi</span>
                                                </th>
                                            </tr>
                                        </thead>

                                        <tbody>
                                        <?php foreach ($products as $value) { 
                                            $operators_data = $this->operators->get_by_id($value->operator_id); ?>
                                            
                                            <tr class="even pointer">
                                                <td class=" "><?php echo $operators_data->nama; ?></td>
                                                <td class=" "><?php echo $value->tipe_pembelian; ?></td>
                                                <td class=" "><?php echo $value->keterangan; ?></td>
                                                <td class=" "><?php echo "Rp ".number_format($value->harga, 0, '', '.');; ?></td>
                                                <td class=" "><?php echo $value->kode_sms; ?></td>
                                                <td class="last"><a href="admin/ubah_pulsa/<?php echo $value->product_id; ?>">Ubah</a>
                                                </td>
                                            </tr>
                                        <?php } ?>
                                        </tbody>

                                    </table>
                                </div>
                            </div>
                        </div>

                        <br />
                        <br />
                        <br />

                    </div>
                </div>
                    
                </div>
                <!-- /page content -->
            </div>

        </div>

        <div id="custom_notifications" class="custom-notifications dsp_none">
            <ul class="list-unstyled notifications clearfix" data-tabbed_notifications="notif-group">
            </ul>
            <div class="clearfix"></div>
            <div id="notif-group" class="tabbed_notifications"></div>
        </div>

        <script src="js/bootstrap.min.js"></script>

        <script src="js/progressbar/bootstrap-progressbar.min.js"></script>
        <script src="js/nicescroll/jquery.nicescroll.min.js"></script>
        <script src="js/custom.js"></script>

        <!-- Datatables -->
        <script src="js/datatables/js/jquery.dataTables.js"></script>
        <script src="js/datatables/tools/js/dataTables.tableTools.js"></script>
        
        <!-- pace -->
        <script src="js/pace/pace.min.js"></script>
        <script>
            var asInitVals = new Array();
            $(document).ready(function () {
                var oTable = $('#example').dataTable({
                    "oLanguage": {
                        "sSearch": "Search all columns:"
                    },
                    "aoColumnDefs": [
                        {
                            'bSortable': true,
                            'aTargets': [0]
                        } //disables sorting for column one
            ],
                    'iDisplayLength': 10,
                    "sPaginationType": "full_numbers"
                });
                $("tfoot input").keyup(function () {
                    /* Filter on the column based on the index of this element's parent <th> */
                    oTable.fnFilter(this.value, $("tfoot th").index($(this).parent()));
                });
                $("tfoot input").each(function (i) {
                    asInitVals[i] = this.value;
                });
                $("tfoot input").focus(function () {
                    if (this.className == "search_init") {
                        this.className = "";
                        this.value = "";
                    }
                });
                $("tfoot input").blur(function (i) {
                    if (this.value == "") {
                        this.className = "search_init";
                        this.value = asInitVals[$("tfoot input").index(this)];
                    }
                });
            });
        </script>
</body>

</html>