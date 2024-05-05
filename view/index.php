<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">

    <?php require_once('../bootstrap/style.php'); ?>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="./style.css">

    <title>Osbb</title>
</head>
<body>
    <div class="container-md mt-4 position-relative">

        <div style="position: relative; z-index: 1;">
            <div id="alert_permission" class="position-fixed p-1 bottom-0 left-0 mb-3 d-flex" style="transform: translateY(150%); transition: transform .2s ease-in;">
                <span class="card d-flex align-items-center justify-content-center py-3 px-3 border-end-0 rounded-end-0 bg-warning">
                    <i class="fa-solid fa-circle-exclamation"></i>
                </span>
                <span class="card align-items-center justify-content-center py-3 px-3 rounded-start-0 text-custom" id="permission_message">
                </span>
            </div>
        </div>

        <div class="d-flex justify-content-between flex-row align-items-center my-3" >
            <h1 class="text-title m-0">Welcome, <?php echo $_GET['username']; ?>!</h1>

            <div class="d-flex justify-content-between flex-row align-items-center gap-2">
                <h3 class="m-0 text-title"> Count entry:
                    <?php
                        $username = $_GET['username'];

                        $get_data = file_get_contents('../counter.dat');
                        $revert_to_arr = preg_split("/\n\s*/", $get_data);

                        $countR = 0;
                        foreach ($revert_to_arr as $row){
                            if(!empty($row)){
                                list($name, $count) = explode(": ", $row, 2);

                                if($name === $username){
                                    $countR = (int)$count;
                                }
                            }
                        }
                        echo $countR;
                    ?>
                </h3>
                <button type="button" id="exit" class="btn btn-danger button_text" >Exit</button>

                <?php
                    require_once('../sqlMain.php');
                    $sql_data = new MainSql(null, null);
                    $username = $sql_data->username;
                ?>

                <button id="operate_permission" type="button" class="btn btn-secondary button_text" <?php if($username !== 'root') echo 'disabled'; ?> >Permission</button>
            </div>
        </div>

        <div class="d-flex justify-content-between">
            <button type="button" id="add_btn" class="btn btn-primary rounded-end-0 py-2 px-3 shadow-none button_text">Add</button>
            <input type="search" id="search_input" class="form-control rounded-start-0 rounded-end-0 shadow-none button_text" placeholder="Search by owner full name" />
            <button disabled type="button" id="search_btn" class="btn btn-primary rounded-start-0 py-2 px-4 shadow-none button_text">Search</button>
        </div>

         <table id="table" class="table table-bordered my-4 text_table">
                <thead>
                    <tr>
                        <th>Entrance Number</th>
                        <th>Flat Number</th>
                        <th>Owner Firstname</th>
                        <th>Owner Secondname</th>
                        <th>Owner Phone Number</th>
                        <th>Services Price</th>
                        <th>Tenants</th>
                        <th>Options</th>
                    </tr>
                </thead>
                <tbody id="body_table">

                    <?php
                        $data = $sql_data->getData();
                        if(!empty($data)):
                            foreach ($data as $dataRow):
                    ?>
                            <tr class="td_padding" data-entry="<?php echo $dataRow['id']; ?>">
                                <td class="entrance_number" ><?php echo $dataRow['entrance_number']; ?></td>
                                <td class="flat_number" ><?php echo $dataRow['flat_number']; ?></td>
                                <td class="owner_firstname" ><?php echo $dataRow['owner_firstname']; ?></td>
                                <td class="owner_secondname" ><?php echo $dataRow['owner_secondname']; ?></td>
                                <td class="phone_number_owner" ><?php echo $dataRow['phone_number_owner']; ?></td>
                                <td class="services_price" ><?php echo $dataRow['services_price']; ?></td>
                                <td class="tenants" ><?php echo $dataRow['tenants']; ?></td>
                                <td>
                                    <div class="d-flex align-items-center flex-row">
                                        <button data-delete-entry="<?php echo $dataRow['id']; ?>" type="button" class="border border-end-0 rounded rounded-end-0 delete-btn">
                                            <i class='fa fa-trash'></i>
                                        </button>
                                        <button data-update-entry="<?php echo $dataRow['id']; ?>" type="button" class="border rounded rounded-start-0 update-btn">
                                            <i class="fa fa-pen"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                    <?php
                            endforeach;
                        else:
                    ?>
                        <tr>
                            <td colspan="8" class="text-center fs-3" >Table is Empty!</td>
                        </tr>
                    <?php
                        endif;
                    ?>
                </tbody>
         </table>
    </div>

    <div class="modal fade" id="add_update_modal">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="add_update_form">
                    <div class="modal-header">
                        <div class="modal-title fs-2 text-title" id="title_modal"></div>
                        <button id="close-modal-header" type="button" class="btn btn-close shadow-none button_text" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <input id="update_id" type="hidden" value="" name="update_id">

                        <div class="my-3" >
                            <label class="text-custom" for="entrance_number">Entrance Number</label>
                            <input type="number" min="1" class="form-control shadow-none text-custom" name="entrance_number" id="entrance_number" autofocus>
                            <span id="err_entrance_number" class="text text-danger text-custom"></span>
                        </div>
                        <div class="my-3" >
                            <label class="text-custom" for="flat_number">Flat Number</label>
                            <input type="text" class="form-control shadow-none text-custom" name="flat_number" id="flat_number">
                            <span id="err_flat_number" class="text text-danger text-custom"></span>
                        </div>
                        <div class="my-3" >
                            <label class="text-custom" for="owner_firstname">Owner Firstname</label>
                            <input type="text" class="form-control shadow-none text-custom" name="owner_firstname" id="owner_firstname">
                            <span id="err_owner_firstname" class="text text-danger text-custom"></span>
                        </div>
                        <div class="my-3" >
                            <label class="text-custom" for="owner_secondname">Owner Secondname</label>
                            <input type="text" class="form-control shadow-none text-custom" name="owner_secondname" id="owner_secondname">
                            <span id="err_owner_secondname" class="text text-danger text-custom"></span>
                        </div>
                        <div class="my-3" >
                            <label class="text-custom" for="phone_number_owner">Owner Phone Number</label>
                            <input type="text" class="form-control shadow-none text-custom" name="phone_number_owner" id="phone_number_owner">
                            <span id="err_phone_number_owner" class="text text-danger text-custom"></span>
                        </div>
                        <div class="my-3" >
                            <label class="text-custom" for="services_price">Services Price</label>
                            <input type="text" class="form-control shadow-none text-custom" name="services_price" id="services_price">
                            <span id="err_services_price" class="text text-danger text-custom"></span>
                        </div>
                        <div class="my-3" >
                            <label class="text-custom" for="tenants">Tenants</label>
                            <input type="text" class="form-control shadow-none text-custom" name="tenants" id="tenants">
                            <span id="err_tenants" class="text text-danger text-custom"></span>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button id="close-modal-footer" type="button" class="btn btn-danger shadow-none button_text" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary shadow-none button_text" id="submit_button"></button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div id="delete_modal" class="modal fade">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="delete_form">
                    <div class="modal-header">
                        <div class="modal-title fs-2 shadow-none text-title">Delete</div>
                        <button class="btn btn-close shadow-none button_text" type="button" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" value="" name="delete_id">

                        <span class="text-custom">Are you sure you want delete?</span><br>
                        <span class="text-custom">Entry number: <b class="text-custom" id="delete_entrance_number"></b></span><br>
                        <span class="text-custom">Flat number: <b class="text-custom" id="delete_flat_number"></b></span><br>
                        <span class="text-custom">Owner: <b class="text-custom" id="delete_owner"></b></span><br>
                    </div>
                    <div class="modal-footer">
                        <button id="close-delete-modal-footer" class="btn btn-danger shadow-none button_text" type="button" data-bs-dismiss="modal">Cancel</button>
                        <button class="btn btn-secondary shadow-none button_text" type="submit">Delete</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="exit_modal">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="exit_form" >
                    <div class="modal-header">
                        <div class="modal-title fs-2 text-title">Exit</div>
                        <button class="btn btn-close shadow-none button_text" data-bs-dismiss="modal" type="button"></button>
                    </div>
                    <div class="modal-body">
                        <span class="text-custom">Are you sure you want to exit <b class="text-custom"><?php echo $_GET['username']; ?></b>?</span>
                    </div>
                    <div class="modal-footer">
                        <button id="close-exit-modal-footer" class="btn btn-danger shadow-none button_text" type="button" data-bs-dismiss="modal">Cancel</button>
                        <button class="btn btn-secondary shadow-none button_text" type="submit">Exit</button>
                    </div>
                </form>
            </div>
        </div>
    </div>


<?php require_once('../bootstrap/script.php'); ?>
<script src="./script.js" defer ></script>
</body>
</html>