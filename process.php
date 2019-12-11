<?php session_start(); 
if (isset($_POST['save'])) {

    $type = $_POST['type'];
    $sn_textarea = $_POST['sn'];

    mysqli_report(MYSQLI_REPORT_ALL);

    try {

        if (!preg_match("/^\d+$/", $type)) {
            throw new Exception('Выберите тип оборудования из выпадающего списка');
        }
        if (empty($sn_textarea)) {
            throw new Exception('Введите серийные номера');
        }

        $mysqli = new mysqli('localhost', 'mysql', 'mysql', 'test_task1');

        $equipment_type_mask = $mysqli->query("SELECT mask FROM equipment_types WHERE id = '$type'");                
        
        $mask = $equipment_type_mask->fetch_assoc()['mask'];
        
        $mask_expr = '/' . preg_replace(['/N/', '/Z/', '/A/', '/a/', '/X/'], ['[0-9]', '[-_@]', '[A-Z]', '[a-z]','[0-9A-Z]'], $mask) . '/';
        
        $sn_arr=explode("\n", $sn_textarea);

        $wrong_sn = ''; 
        $has_wrong_sn = false;
        foreach ($sn_arr as $sn) {
            if (preg_match($mask_expr, $sn)) {
                try {
                    $mysqli->query("INSERT INTO equipments (type, sn) VALUES('$type', '$sn')");  
                } catch (Exception $e) {
                    $wrong_sn .= $sn . ', ';
                }                                           
            } else {                
                $wrong_sn .= $sn . ', ';
                $has_wrong_sn = true;
            }                
        }
        if ($has_wrong_sn) {
            throw new Exception("Серийные номера '$wrong_sn' не соответствуют маске '$mask' или уже существуют");
        } else {
            $message = "Все записи успешно сохранены!";
            $msg_type = 'success';      
        }
    } catch (Exception $e) {
        $message = $e->getMessage();
        $msg_type = 'danger';
    } finally {
        $_SESSION['message'] = $message;
        $_SESSION['msg_type'] = $msg_type;
        header("location: index.php");    
    }
}
