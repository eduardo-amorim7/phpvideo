<?php
$uuids = AVideoPlugin::getPluginsOnByDefault();
$rowId = array();
foreach ($uuids as $value) {
    $rowId[] = " row.uuid != '{$value}' ";
}
$uuidJSCondition = implode(" && ", $rowId);
?>
<style>
    td.wrapText{white-space: normal;}
    .PluginActive, .PluginTags{
        border: solid 2px;
    }
    .PluginActive.checked, .PluginTags.checked{
    }
    .PluginActive.unchecked, .PluginTags.unchecked{
        background-color: rgba(0,0,0,0.4);
    }
    .PluginActive:hover, .PluginTags:hover{
        border: solid 2px rgba(0,0,0,1);
        cursor: pointer;
    }
    .pluginDescription{
        white-space: nowrap;
        text-overflow: ellipsis;
        overflow: hidden;
        height: 1.75em;
        line-height: 1.75;
    }
</style>
<div class="container-fluid">
    <div class="panel panel-default">
        <div class="panel-heading tabbable-line">
            <ul class="nav nav-tabs">
                <li class="active"><a data-toggle="tab" href="#menu0"><i class="fa fa-plug"></i> <?php echo __('Installed Plugins'); ?></a></li>
                <li><a data-toggle="tab" href="#menu1"><i class="fa fa-cart-plus"></i> <?php echo __('Plugins Store'); ?></a></li>
            </ul>
        </div>
        <div class="panel-body">
            <div class="tab-content">
                <div id="menu0" class="tab-pane fade in active">
                    <div class="list-group-item">
                        <div class="btn-group" >
                            <button type="button" class="btn btn-default" id="upload">
                                <i class="fas fa-plus"></i> <?php echo __("Upload a Plugin"); ?>
                            </button>
                        </div>
                        <div style="text-align: right; padding: 5px;">
                            <span class="badge" id="PluginTagsTotal">...</span>
                            <button class="label label-default checked PluginTags PluginActive" pluginTag="all" id="PluginTagsAll" onclick="resetShowActiveInactiveOnly();PluginTagsReset();" >
                                <i class="fas fa-check-double"></i> <span class="hidden-md hidden-sm hidden-xs"><?php echo __("All"); ?></span>
                            </button>
                            <button class="label label-primary checked PluginActive" pluginTag="Installed" id="PluginTagsInstalled" onclick="showActivesOnly();" >
                                <i class="fas fa-check"></i> <span class="hidden-md hidden-sm hidden-xs"><?php echo __("Installed"); ?></span>
                            </button>
                            <button class="label label-primary checked PluginActive" pluginTag="Uninstalled" id="PluginTagsUninstalled" onclick="showInactiveOnly();" >
                                <i class="fas fa-times"></i> <span class="hidden-md hidden-sm hidden-xs"><?php echo __("Uninstalled"); ?></span>
                            </button>
                            <?php
                            $class = new ReflectionClass('PluginTags');
                            $staticProperties = $class->getStaticProperties();
                            foreach ($staticProperties as $key => $value) {
                                ?>
                                <button class="label label-<?php echo $value[0]; ?> unchecked PluginTags" 
                                        id="PluginTags<?php echo $value[3]; ?>" 
                                        pluginTag="<?php echo $value[3]; ?>" 
                                        onclick="PluginTagsToggle('<?php echo $value[3]; ?>')"
                                        data-toggle="tooltip" title="<?php echo __($value[1]); ?>">
                                    <?php echo $value[2]; ?> <span class="hidden-md hidden-sm hidden-xs"><?php echo __($value[1]); ?></span>
                                </button>
                                <?php
                            }
                            ?>
                        </div>
                        <table id="grid" class="table table-condensed table-hover table-striped">
                            <thead>
                                <tr>
                                    <th data-column-id="name" data-formatter="name" data-width="300px" ><?php echo __("Name"); ?></th>
                                    <th data-column-id="description" data-formatter="description" data-css-class="wrapText hidden-md hidden-sm hidden-xs" data-header-css-class="hidden-md hidden-sm hidden-xs"><?php echo __("description"); ?></th>
                                    <th data-column-id="commands" data-formatter="commands" data-sortable="false" data-width="150px"></th>
                                </tr>
                            </thead>
                        </table>
                        <div id="pluginsFormModal" class="modal fade" tabindex="-1" role="dialog">
                            <div class="modal-dialog" role="document">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                                        <h4 class="modal-title"><?php echo __("Plugin Form"); ?></h4>
                                    </div>
                                    <div class="modal-body">
                                        <ul class="nav nav-tabs">
                                            <li class="active"><a data-toggle="tab" href="#visual">Visual</a></li>
                                            <li><a data-toggle="tab" href="#code">Code</a></li>
                                        </ul>
                                        <div class="tab-content">
                                            <div id="visual" class="tab-pane fade in active">
                                                <div class="row" id="jsonElements" style="padding: 10px;">Some content.</div>
                                            </div>
                                            <div id="code" class="tab-pane fade">
                                                <form class="form-compact"  id="updatePluginForm" onsubmit="">
                                                    <input type="hidden" id="inputPluginId"  >
                                                    <label for="inputData" class="sr-only">Object Data</label>
                                                    <textarea class="form-control" id="inputData"  rows="5"  placeholder="Object Data"></textarea>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo __("Close"); ?></button>
                                        <button type="button" class="btn btn-primary" id="savePluginBtn"><?php echo __("Save changes"); ?></button>
                                    </div>
                                </div><!-- /.modal-content -->
                            </div><!-- /.modal-dialog -->
                        </div>
                        <div id="pluginsImportFormModal" class="modal fade" tabindex="-1" role="dialog">
                            <div class="modal-dialog" role="document">
                                <?php
                                $dir = "{$global['systemRootPath']}plugin";
                                if (!isUnzip()) {
                                    ?>
                                    <div class="alert alert-warning">
                                        <?php echo __("Make sure you have the unzip app on your server"); ?>
                                        <pre><code>sudo apt-get install unzip</code></pre>
                                    </div>
                                    <?php
                                }
                                if (is_writable($dir)) {
                                    ?>
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                                            <h4 class="modal-title"><?php echo __("Upload a Plugin ZIP File"); ?></h4>
                                        </div>
                                        <div class="modal-body">
                                            <input id="input-b1" name="input-b1" type="file" class="">
                                        </div>
                                    </div>
                                    <?php
                                } else {
                                    ?>
                                    <div class="alert alert-danger">
                                        <?php echo __("You need to make the plugin dir writable before upload, run this command and refresh this page"); ?>
                                        <pre><code>chown www-data:www-data <?php echo $dir; ?> && chmod 755 <?php echo $dir; ?></code></pre>
                                    </div>
                                    <?php
                                }
                                ?>
                            </div>
                        </div>
                    </div>
                </div>
                <div id="menu1" class="tab-pane fade">
                    <div class="list-group-item">
                        <div class="panel panel-default">
                            <div class="panel-heading"><a href="https://youphp.tube/marketplace/?tab=plugin" class="btn btn-default btn-xs"><i class="fa fa-plug"></i> Plugin Store </a></div>
                            <div class="panel-body">
                                <ul class="list-group" id="pluginStoreList">
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <li class="list-group-item hidden col-md-3" id="pluginStoreListModel">
                <div class="panel panel-warning panel-sm">
                    <div class="panel-heading">
                        <h3 class="panel-title"></h3>
                    </div>
                    <div class="panel-body">
                        <div class="the-price">
                            <h1>
                                USD $<span class="int">0</span>.<small class="cents">00</small>
                            </h1>
                        </div>
                        <table class="table">
                            <tr >
                                <td>
                                    <img src="" class="img img-responsive img-rounded img-thumbnail zoom" style="height: 70px;">
                                </td>
                            </tr>
                            <tr class="active">
                                <td class="desc" style="height: 50px;"></td>
                            </tr>
                        </table>
                    </div>
                    <div class="panel-footer">
                        <a href="https://youphp.tube/plugins/?tab=plugin" class="btn btn-success btn-xs" role="button"><i class="fa fa-cart-plus"></i> <?php echo __("Buy This Plugin"); ?> </a>
                    </div>
                </div>
            </li>
        </div>
    </div>
</div><!--/.container-->

<div id="pluginsPermissionModal" class="modal fade" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div id="pluginsPermissionModalContent">

        </div>
    </div>
</div>
<script src="<?php echo getCDN(); ?>js/form2JSON.js" type="text/javascript"></script>
<script>

    function createPluginStoreList(src, name, price, description) {
        var intPrice = Math.floor(price);
        //var cents = Math.ceil((price - intPrice) * 100);
        var $li = $('#pluginStoreListModel').clone();
        $li.removeClass("hidden").attr("id", "");
        $li.find('.panel-title').text(name);
        $li.find('.int').text(intPrice);
        $li.find('.cents').text("99");
        $li.find('.desc').html(description);
        $li.find('.img').attr("src", src);
        $('#pluginStoreList').append($li);
    }

    function showActivesOnly() {
        var id = "#PluginTagsUninstalled";
        $(id).removeClass('checked');
        $(id).addClass('unchecked');
        var id = "#PluginTagsInstalled";
        $(id).removeClass('unchecked');
        $(id).addClass('checked');
        processShow();
    }
    function showInactiveOnly() {
        var id = "#PluginTagsInstalled";
        $(id).removeClass('checked');
        $(id).addClass('unchecked');
        var id = "#PluginTagsUninstalled";
        $(id).removeClass('unchecked');
        $(id).addClass('checked');
        processShow();
    }

    function resetShowActiveInactiveOnly() {
        var id = "#PluginTagsInstalled";
        $(id).removeClass('unchecked');
        $(id).addClass('checked');
        var id = "#PluginTagsUninstalled";
        $(id).removeClass('unchecked');
        $(id).addClass('checked');
    }

    function showAllOnProcess() {
        if (isFilterInstalledUninstalledDisabled() || $("#PluginTagsAll").hasClass('checked')) {
            return true;
        }
        return false;
    }

    function isFilterInstalledUninstalledDisabled() {
        var id1 = "#PluginTagsInstalled";
        var id2 = "#PluginTagsUninstalled";
        if ($(id1).hasClass('checked') && $(id2).hasClass('checked')) {
            return false;
        }
        if ($(id1).hasClass('unchecked') && $(id2).hasClass('unchecked')) {
            return false;
        }
        return true;
    }

    function processShowHideIfActive(tr) {
        if ($(tr).find(".pluginSwitch").is(":checked")) {
            if ($("#PluginTagsInstalled").hasClass('checked')) {
                $(tr).show();
            } else {
                $(tr).hide();
            }
        } else {
            if ($("#PluginTagsInstalled").hasClass('checked')) {
                $(tr).hide();
            } else {
                $(tr).show();
            }
        }
    }

    function processShow() {
        if (!isFilterInstalledUninstalledDisabled()) {
            PluginTagsProcess();
        } else {
            var allItemsSeletors = getAllItemsSelector();
            //console.log(allItemsSeletors);
            $("#grid tr").each(function (i, tr) {

                if (allItemsSeletors) {
                    if ($(tr).find(allItemsSeletors).length !== 0) {
                        processShowHideIfActive(tr);
                    } else {
                        $(tr).hide();
                    }
                } else {
                    processShowHideIfActive(tr);
                }


                //console.log($(tr).find(allItemsSeletors).length);
                if (!allItemsSeletors || $(tr).find(allItemsSeletors).length !== 0) {
                    if ($(tr).find(".pluginSwitch").is(":checked")) {
                        if ($("#PluginTagsInstalled").hasClass('checked')) {
                            $(tr).show();
                        } else {
                            $(tr).hide();
                        }
                    } else {
                        if ($("#PluginTagsInstalled").hasClass('checked')) {
                            $(tr).hide();
                        } else {
                            $(tr).show();
                        }
                    }
                }
            });
        }

        totalVisible();
    }
    function PluginTagsReset() {
        $('.PluginTags').not('#PluginTagsAll').removeClass('checked');
        $('.PluginTags').not('#PluginTagsAll').addClass('unchecked');
        $("#PluginTagsAll").removeClass('unchecked');
        $("#PluginTagsAll").addClass('checked');
        $("#grid tr").show();
        totalVisible();
    }

    function totalVisible() {
        $('#PluginTagsTotal').text($("#grid tr:visible").length + ' / ' + $("#grid tr").length);
    }

    function PluginTagsToggle(type) {
        var id = '#PluginTags' + type;
        if ($(id).hasClass('checked')) {
            $(id).removeClass('checked');
            $(id).addClass('unchecked');
        } else {
            $(id).removeClass('unchecked');
            $(id).addClass('checked');
        }
        $('#PluginTagsAll').removeClass('checked');
        $('#PluginTagsAll').addClass('unchecked');
        processShow();
    }

    function getAllItemsSelector() {
        var selectors = [];
        $('.PluginTags').each(function (i, obj) {
            if ($(obj).hasClass('checked')) {
                selectors.push('.plugin' + $(obj).attr('pluginTag'));
            }
        });
        if ($("#PluginTagsAll").hasClass('checked') || selectors.length === 0) {
            PluginTagsReset();
            return false;
        }
        return selectors.join(", ");
    }

    function PluginTagsProcess() {
        var allItemsSeletors = getAllItemsSelector();
        $("#grid tr").each(function (i, tr) {
            if (!allItemsSeletors || $(tr).find(allItemsSeletors).length !== 0) {
                $(tr).show();
            } else {
                $(tr).hide();
            }
        });
        totalVisible();
    }

    function tooglePluginDescription(t) {
        if ($(t).parent().hasClass('pluginDescription')) {
            $(t).parent().removeClass('pluginDescription');
            $(t).find('i').removeClass('fa-plus');
            $(t).find('i').addClass('fa-minus');
        } else {
            $(t).parent().addClass('pluginDescription');
            $(t).find('i').addClass('fa-plus');
            $(t).find('i').removeClass('fa-minus');
        }
    }


    function pluginPermissionsBtn(plugins_id) {
        modal.showPleaseWait();
        $("#pluginsPermissionModalContent").html('');
        $.ajax({
            url: '<?php echo $global['webSiteRootURL']; ?>plugin/Permissions/getPermissionsFromPlugin.html.php?plugins_id=' + plugins_id,
            success: function (response) {
                modal.hidePleaseWait();
                $("#pluginsPermissionModalContent").html(response);
                $('#pluginsPermissionModal').modal();
            }
        });
    }

    $(document).ready(function () {


        var myTextarea = document.getElementById("inputData");
        var grid = $("#grid").bootgrid({
            labels: {
                noResults: "<?php echo __("No results found!"); ?>",
                all: "<?php echo __("All"); ?>",
                infos: "<?php echo __("Showing {{ctx.start}} to {{ctx.end}} of {{ctx.total}} entries"); ?>",
                loading: "<?php echo __("Loading..."); ?>",
                refresh: "<?php echo __("Refresh"); ?>",
                search: "<?php echo __("Search"); ?>",
            },
            navigation: 0,
            ajax: true,
            url: "<?php echo $global['webSiteRootURL'] . "objects/pluginsAvailable.json.php"; ?>",
            responseHandler: function (data) {
                setTimeout(function () {
                    processShow();
                    totalVisible();
                }, 1000);
                return data;

            },
            formatters: {
                "commands": function (column, row) {
                    var editBtn = '';

                    if (row.id && !$.isEmptyObject(row.data_object)) {
                        editBtn = '<button type="button" class="btn btn-xs btn-default command-edit  btn-block" data-row-id="' + row.id + '" data-toggle="tooltip" data-placement="left" title="Edit"><span class="glyphicon glyphicon-edit" aria-hidden="true"></span> <?php echo __('Edit parameters'); ?></button>';
                    }
                    var sqlBtn = '';
                    if (row.databaseScript && row.isPluginTablesInstalled) {
                        //sqlBtn = '<button type="button" class="btn btn-xs btn-default command-sql  btn-block" data-row-id="' + row.id + '" data-toggle="tooltip" data-placement="left" title="Run Database Script"><span class="fa fa-database" aria-hidden="true"></span> <?php echo __('Reinstall tables'); ?></button>';
                    }
                    menu = '';
                    if (row.installedPlugin && row.installedPlugin.status == 'active') {
                        menu = row.pluginMenu;
                    }

                    return  editBtn + sqlBtn + menu;
                },
                "name": function (column, row) {
                    var checked = "";
                    var switchBtn = '';
                    if (<?php echo $uuidJSCondition; ?>) {
                        if(row.isPluginTablesInstalled || !row.databaseScript || (row.hasOwnProperty("installedPlugin") && row.installedPlugin.hasOwnProperty("pluginversion"))){
                            if (row.enabled) {
                                checked = " checked='checked' ";
                            }
                            switchBtn = '<div class="material-small material-switch pull-left"><input name="enable' + row.uuid + '" id="enable' + row.uuid + '" type="checkbox" value="0" class="pluginSwitch" ' + checked + ' /><label for="enable' + row.uuid + '" class="label-success"></label></div>';
                        }
                        
                    } else {
                        if (!row.enabled) {
                            $.ajax({
                                url: '<?php echo $global['webSiteRootURL']; ?>objects/pluginSwitch.json.php',
                                data: {"uuid": row.uuid, "name": row.name, "dir": row.dir, "enable": true},
                                type: 'post',
                                success: function (response) {}
                            });
                        }
                        switchBtn = '';
                    }
                    if(!row.isPluginTablesInstalled){
                        switchBtn += '<button type="button" class="btn btn-xs btn-danger command-sql  btn-block" data-row-id="' + row.id + '" data-toggle="tooltip" data-placement="right" title="Run Database Script"><span class="fa fa-database" aria-hidden="true"></span> <?php echo __('Install tables'); ?></button>';
                    }
                    //var txt = '<span id="plugin' + row.uuid + '" style="margin-top: -60px; position: absolute;"></span><a href="#plugin' + row.uuid + '">' + row.name + "</a> (" + row.dir + ")<br><small class='text-muted'>UUID: " + row.uuid + "</small>";
                    var txt = '<span id="plugin' + row.uuid + '" style="margin-top: -60px; position: absolute;"></span><a href="#plugin' + row.uuid + '">' + row.name + "</a> <small class='text-muted'>(" + row.dir + ")</small>";


                    txt += "<br> " + switchBtn + ' &nbsp; ';
                    if (row.hasOwnProperty("installedPlugin") && row.installedPlugin.hasOwnProperty("pluginversion")) {
                        //console.log("Objecto: " + row.name);
                        //console.log("Installed: " + row.installedPlugin.pluginversion);
                        //console.log("Object: " + row.pluginversion);
                        //console.log(row.installedPlugin.pluginversion != row.pluginversion);
                        if (row.installedPlugin.pluginversion != row.pluginversion) {
                            txt += "<small class='text-danger'>Installed (@" + row.installedPlugin.pluginversion + ")<br>Current Version (@" + row.pluginversion + "), please update</small>";
                            txt += '<div class="clearfix"></div><button type="button" class="btn btn-xs btn-warning command-update btn-block" data-row-id="' + row.id + '" data-toggle="tooltip" data-placement="right" title="<?php echo __('Run Update Script'); ?>"><span class="fa fa-wrench" aria-hidden="true"></span> <?php echo __('Update'); ?> @' + row.pluginversion + '</button>';
                        } else {
                            txt += "<small class='text-success'>Version: @" + row.pluginversion + "</small>";
                        }
                    }
                    if (row.hasOwnProperty("permissions") && row.permissions.length) {
                        var disabled = "";
                        if (!row.isPluginTablesInstalled) {
                            disabled = ' disabled="disabled" ';
                        }
                        txt += '<button ' + disabled + ' type="button" class="btn btn-xs btn-default btn-block" onclick="pluginPermissionsBtn(' + row.id + ')" data-toggle="tooltip" data-placement="right" title="<?php echo __('User Groups Permissions'); ?>"><span class="fa fa-users" aria-hidden="true"></span> <?php echo __('User Groups Permissions'); ?></button>';
                    }

                    return txt;
                },
                "description": function (column, row) {
                    var txt = '<div class="pluginDescription"><button class="btn btn-xs btn-default" onclick="tooglePluginDescription(this);"><i class="fas fa-plus"></i></button> ' + row.description + '</div>';
                    var tags = '';
                    if (row.tags) {
                        for (i = 0; i < row.tags.length; i++) {
                            if (typeof row.tags[i] == 'object') {
                                tags += '<span class="label label-' + row.tags[i][0] + ' plugin' + row.tags[i][3] + '">' + row.tags[i][2] + ' ' + row.tags[i][1] + '</span> ';
                            } else {
                                if (row.tags[i] === 'update') {
                                    tags += '<a class="label label-warning" href="https://youphp.tube/marketplace/" target="_blank">Update Available: v' + row.pluginversionMarketPlace + '</a> ';
                                } else {
                                    var cl = "primary";
                                    if (row.tags[i] === 'free') {
                                        cl = 'success';
                                    } else if (row.tags[i] === 'firstPage') {
                                        cl = 'danger';
                                    } else if (row.tags[i] === 'login') {
                                        cl = 'info';
                                    }

                                    tags += '<span class="label label-' + cl + '">' + row.tags[i] + '</span> ';
                                }
                            }
                        }
                    }
                    txt += "<br>" + tags;
                    return txt;
                }
            }
        }).on("loaded.rs.jquery.bootgrid", function () {
            $('[data-toggle="tooltip"], .tooltip').tooltip("hide");
            setTimeout(function(){
                $('[data-toggle="tooltip"]').tooltip({container: 'body'});
            },500);
            /* Executes after data is loaded and rendered */
            grid.find(".pluginSwitch").on("change", function (e) {
                var row_index = $(this).closest('tr').index();
                var row = $("#grid").bootgrid("getCurrentRows")[row_index];
                modal.showPleaseWait();
                $.ajax({
                    url: '<?php echo $global['webSiteRootURL']; ?>objects/pluginSwitch.json.php',
                    data: {"uuid": row.uuid, "name": row.name, "dir": row.dir, "enable": $('#enable' + row.uuid).is(":checked")},
                    type: 'post',
                    success: function (response) {
                        modal.hidePleaseWait();
                        $("#grid").bootgrid('reload');
                    }
                });
            });
            grid.find(".command-edit").on("click", function (e) {
                var row_index = $(this).closest('tr').index();
                var row = $("#grid").bootgrid("getCurrentRows")[row_index];
                $('#inputPluginId').val(row.id);
                var json = JSON.stringify(row.data_object);
                //console.log(json);
                //console.log(row.data_object);
                jsonToForm(row.data_object, row.data_object_helper);
                $('#inputData').val(json);
                $('#pluginsFormModal').modal();
            });
            grid.find(".command-sql").on("click", function (e) {
                var row_index = $(this).closest('tr').index();
                var row = $("#grid").bootgrid("getCurrentRows")[row_index];
                $('#inputPluginId').val(row.id);
                $('#inputData').val(JSON.stringify(row.data_object));
                modal.showPleaseWait();
                $.ajax({
                    url: '<?php echo $global['webSiteRootURL']; ?>objects/pluginRunDatabaseScript.json.php',
                    data: {"name": row.name},
                    type: 'post',
                    success: function (response) {
                        if(response.error){
                            avideoAlertError(response.msg) ;                               
                        }else{
                            $("#grid").bootgrid('reload');
                        }
                        modal.hidePleaseWait();
                    }
                });
            });
            grid.find(".command-update").on("click", function (e) {
                var row_index = $(this).closest('tr').index();
                var row = $("#grid").bootgrid("getCurrentRows")[row_index];
                $('#inputPluginId').val(row.id);
                $('#inputData').val(JSON.stringify(row.data_object));
                modal.showPleaseWait();
                $.ajax({
                    url: '<?php echo $global['webSiteRootURL']; ?>objects/pluginRunUpdateScript.json.php',
                    data: {"name": row.name},
                    type: 'post',
                    success: function (response) {
                        modal.hidePleaseWait();
                        $("#grid").bootgrid('reload');
                    }
                });
            });
        });
        $('#savePluginBtn').click(function (evt) {
            modal.showPleaseWait();
            $.ajax({
                url: '<?php echo $global['webSiteRootURL']; ?>objects/pluginAddDataObject.json.php',
                data: {"id": $('#inputPluginId').val(), "object_data": $('#inputData').val()},
                type: 'post',
                success: function (response) {
                    modal.hidePleaseWait();
                    $("#grid").bootgrid('reload');
                    $('#pluginsFormModal').modal('hide');
                }
            });
        });
        $('#upload').click(function (evt) {
            $('#pluginsImportFormModal').modal();
        });
        $('#input-b1').fileinput({
            uploadUrl: '<?php echo $global['webSiteRootURL']; ?>objects/pluginImport.json.php',
            allowedFileExtensions: ['zip']
        }).on('fileuploaded', function (event, data, id, index) {
            $("#grid").bootgrid('reload');
        });
        $.ajax({
            url: 'https://youphp.tube/marketplace/plugins.json?jsonp=1',
            dataType: 'jsonp',
            success: function (response) {
                for (i = 0; i < response.rows.length; i++) {
                    var r = response.rows[i];
                    createPluginStoreList(r.images[0], r.name, r.price, r.description);
                }
            }
        });

    });

</script>
