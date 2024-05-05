<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <?php require_once('../../bootstrap/style.php'); ?>
    <link rel="stylesheet" href="./clean-switch-master/clean-switch.css">
    <link rel="stylesheet" href="./style.css">

    <title>Permission</title>
</head>
<body>

    <?php
        require_once('../../sqlMain.php');
        $sqlMain = new MainSql(null, null);
    ?>

    <div class="container-md my-2 position-relative">

        <div id="alert_success" class="position-fixed bottom-0 mb-4 d-flex" style="transform: translateY(150%); transition: transform .2s ease-in;" >
            <span class="card d-flex align-items-center justify-content-center px-3 py-3 border-end-0 rounded-end-0 bg-success" >
                <i class="fa-solid fa-check"></i>
            </span>
            <span class="text-custom card d-flex align-items-center justify-content-center px-4 py-3 rounded-start-0">
                You are successfully saved data!
            </span>
        </div>


        <div id="alert_success" style="z-index:3;top: 8px;" class="position-fixed start-50 end-0 me-2 alert alert-success d-flex justify-content-between d-none" role="alert">
            <strong class="text-custom">You are successfully saved data!</strong>
        </div>

        <div class="d-flex justify-content-between" >
            <h1 class="text-title" > Permission </h1>
            <button onclick="history.back()" id="back_btn" class="btn btn-secondary button_text" type="button" > Back </button>
        </div>

        <div class="d-flex flex-column my-3" >
            <h3 class="text-title">Entered users</h3>

            <table class="table table-bordered text_table" id="permission_table" >
                <thead>
                    <tr>
                        <th>User</th>
                        <th>Select Priv</th>
                        <th>Insert Priv</th>
                        <th>Update Priv</th>
                        <th>Delete Priv</th>
                    </tr>
                </thead>
                <tbody id="tb_body_permission">

                    <?php
                        $result = $sqlMain->getUsers();
                        if($result && sizeof($result)):
                            foreach ($result as $res):
                    ?>
                        <tr data-user="<?php echo $res['User']; ?>">
                            <td class="user"><?php echo $res['User']; ?></td>
                            <td class="select_switch">
                                <label class="cl-switch cl-switch-small cl-switch-white">
                                    <input data-select="<?php echo $res['User']; ?>" <?php echo $res['Select_priv'] === 'N' ? '' : 'checked'; ?> data-toggle="toggle" class="switch_select" type="checkbox">
                                    <span class="switcher"></span>
                                </label>
                            </td>
                            <td class="insert_switch">
                                <label class="cl-switch cl-switch-small cl-switch-white">
                                    <input data-insert="<?php echo $res['User']; ?>" <?php echo $res['Insert_priv'] === 'N' ? '' : 'checked'; ?> data-toggle="toggle" class="switch_insert" type="checkbox">
                                    <span class="switcher"></span>
                                </label>
                            </td>
                            <td class="update_switch">
                                <label class="cl-switch cl-switch-small cl-switch-white">
                                    <input data-update="<?php echo $res['User']; ?>" <?php echo $res['Update_priv'] === 'N' ? '' : 'checked'; ?> data-toggle="toggle" class="switch_update" type="checkbox">
                                    <span class="switcher"></span>
                                </label>
                            </td>
                            <td class="delete_switch">
                                <label class="cl-switch cl-switch-small cl-switch-white">
                                    <input data-delete="<?php echo $res['User']; ?>" <?php echo $res['Delete_priv'] === 'N' ? '' : 'checked'; ?> data-toggle="toggle" class="switch_delete" type="checkbox">
                                    <span class="switcher"></span>
                                </label>
                            </td>
                        </tr>
                    <?php
                            endforeach;
                        else:
                    ?>
                       <tr>
                           <td colspan="5" class="text-center fs-3">List is Empty</td>
                       </tr>
                    <?php
                        endif;
                    ?>
                </tbody>
            </table>

            <div>
                <button type="button" id="save_changes" class="btn btn-primary button_text">Save Changes</button>
            </div>
        </div>

    </div>

    <script src="./script.js" defer></script>
    <?php require_once('../../bootstrap/script.php'); ?>
</body>
</html>