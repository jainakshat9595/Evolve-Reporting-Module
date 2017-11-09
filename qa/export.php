<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'Classes/PHPExcel.php';

$actual_order_email = array();

// call export function
exportMysqlToCsv();

// export csv
function exportMysqlToCsv()
{

    global $actual_order_email; 
    $conn = dbConnection();

    $objPHPExcel = new PHPExcel();
    
    $objPHPExcel->getProperties()
        ->setCreator("Evolve")
        ->setLastModifiedBy("Evolve")
        ->setTitle("Order Sheet")
        ->setSubject("Order Sheet")
        ->setDescription("Order Sheet")
        ->setKeywords("Order Sheet")
        ->setCategory("Order Sheet");

    $cell_header_style = array(
        'fill' => array(
            'type' => PHPExcel_Style_Fill::FILL_SOLID,
            'color' => array('rgb' => '538dd5')
        ),
        'font' => array(
            'bold'  => true,
            'color' => array('rgb' => '000000'),
            'size'  => 15,
            'name'  => 'Calibri'
        )
    );
    
    getActualOrders($conn, $objPHPExcel, $cell_header_style);

    $objPHPExcel->setActiveSheetIndex(0);

    $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
    $objWriter->save(str_replace('.php', '.xlsx', __FILE__));

    header('Content-type: application/vnd.ms-excel');   
    header('Content-Disposition: attachment; filename="file.xlsx"');
    $objWriter->save('php://output');

    die();
}

function subtractTimeBuffer($date) {
    $timestamp = strtotime($date);
    $timestamp_buff = (12*60*60) + 1800;
    $date_new = date("Y-m-d H:i",($timestamp-$timestamp_buff));
    return $date_new;
}

function addTimeBuffer($date) {
    $timestamp = strtotime($date);
    $timestamp_buff = (12*60*60) + 1800;
    $date_new = date("Y-m-d H:i",($timestamp+$timestamp_buff));
    return $date_new;
}

// db connection function
function dbConnection() {

    // $servername = "localhost";
    // $username = "root";
    // $password = "1234";
    // $dbname = "evolve";

    $servername = "localhost";
    $username = "evolvesn_user2";
    $password = "J.or*aMLwoc@";
    $dbname = "evolvesn_opencart";
    
    // Create connection
    $conn = null;
    try {
        $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }
    catch(PDOException $e) {
        echo "Connection failed: " . $e->getMessage();
    }
    $conn->beginTransaction();
    return $conn;
}

/* 
    ##################################################################      
        For Actual Orders
    ##################################################################
*/
function getActualOrders($conn, $objPHPExcel, $cell_header_style) {

    global $actual_order_email; 

    $sql_query = "SELECT
        oc_order.date_added as 'order_date',
        oc_order.order_id as  'order_id',
        oc_order.customer_id as 'customer_id',
        CONCAT(oc_order.firstname,' ',oc_order.lastname) as 'contact_person',
        oc_order.telephone as 'contact_number',
        oc_order.email as 'contact_email',
        oc_order.payment_method as 'payment_mode',
        oc_order.total as 'payment_total',
        oc_order.payment_city as 'payment_city',
        oc_order.payment_zone_id as 'payment_zone_id',
        CONCAT(oc_order.payment_address_1,' ',oc_order.payment_address_2) as 'payment_address',
        oc_order.payment_postcode as 'payment_postcode',
        oc_order.shipping_city as 'shipping_city',
        oc_order.shipping_zone_id as 'shipping_zone_id',
        CONCAT(oc_order.shipping_address_1,' ',oc_order.shipping_address_2) as 'shipping_address',
        oc_order.shipping_postcode as 'shipping_postcode',
        max(oc_order_history.order_status_id) as 'order_status_id'

        FROM oc_order

        INNER JOIN oc_order_product 
        ON oc_order_product.order_id = oc_order.order_id

        INNER JOIN oc_order_history 
        ON oc_order_history.order_id = oc_order.order_id

        WHERE 
        oc_order.date_added BETWEEN '".subtractTimeBuffer($_POST['start_date'])."' AND '".subtractTimeBuffer($_POST['end_date'])."'
        
        GROUP BY oc_order.order_id";

        // Gets the data from the database
        $q = null;
        try {
            $q = $conn->query($sql_query);
            $q->setFetchMode(PDO::FETCH_ASSOC);
        } catch(PDOException $e) {
            echo "Error: " . $e->getMessage();
        }

        $q_vals = $q->fetchAll();

        $objPHPExcel->setActiveSheetIndex(0);
        $objPHPExcel->getActiveSheet()->setTitle("Actual Orders");

        for($ind = 'A'; $ind<='W'; $ind++) {
            $objPHPExcel->getActiveSheet()->getStyle($ind.'1')->applyFromArray($cell_header_style);
        }

        $objPHPExcel->getActiveSheet()->setCellValue('A1',"S. No.");
        $objPHPExcel->getActiveSheet()->setCellValue('B1',"Order Date"); 
        $objPHPExcel->getActiveSheet()->setCellValue('C1',"Order Id"); 
        $objPHPExcel->getActiveSheet()->setCellValue('D1',"Customer ID");
        $objPHPExcel->getActiveSheet()->setCellValue('E1',"Contact Person"); 
        $objPHPExcel->getActiveSheet()->setCellValue('F1',"Contact Number"); 
        $objPHPExcel->getActiveSheet()->setCellValue('G1',"Email ID");
        $objPHPExcel->getActiveSheet()->setCellValue('H1',"No. of Packets"); 
        $objPHPExcel->getActiveSheet()->setCellValue('I1',"Weights(gms)"); 
        $objPHPExcel->getActiveSheet()->setCellValue('J1',"Mode"); 
        $objPHPExcel->getActiveSheet()->setCellValue('K1',"Amount(Rs)"); 
        $objPHPExcel->getActiveSheet()->setCellValue('L1',"Courier"); 
        $objPHPExcel->getActiveSheet()->setCellValue('M1',"AWB Number");
        $objPHPExcel->getActiveSheet()->setCellValue('N1',"Billing City"); 
        $objPHPExcel->getActiveSheet()->setCellValue('O1',"Billing State");  
        $objPHPExcel->getActiveSheet()->setCellValue('P1',"Billing Address"); 
        $objPHPExcel->getActiveSheet()->setCellValue('Q1',"Billing Pin Code"); 
        $objPHPExcel->getActiveSheet()->setCellValue('R1',"Shipping City"); 
        $objPHPExcel->getActiveSheet()->setCellValue('S1',"Shipping State");  
        $objPHPExcel->getActiveSheet()->setCellValue('T1',"Shipping Address"); 
        $objPHPExcel->getActiveSheet()->setCellValue('U1',"Shipping Pin Code"); 
        $objPHPExcel->getActiveSheet()->setCellValue('V1',"Coupon Code");
        $objPHPExcel->getActiveSheet()->setCellValue('W1',"Product Selection");

        $index = 2;

        if(count($q_vals) == 0) {
            echo "No Records Found";
            die();
        } else {

        foreach($q_vals as $row) {

            if($row['order_status_id'] == 1 || $row['order_status_id'] == 2) {

                array_push($actual_order_email,$row['contact_email']);

                $sub_sql_query_1 = "SELECT
                    oc_order_product.name as 'product_name',
                    oc_order_product.quantity as 'product_quantity',
                    oc_order_product.product_id as 'product_id'
                    FROM oc_order_product
                    WHERE oc_order_product.order_id = ". $row['order_id'];

                $sub_sql_query_2 = "SELECT
                    oc_zone.name as 'payment_state_name'
                    FROM oc_zone
                    WHERE oc_zone.zone_id = ". $row['payment_zone_id'];

                $sub_sql_query_5 = "SELECT
                    oc_zone.name as 'shipping_state_name'
                    FROM oc_zone
                    WHERE oc_zone.zone_id = ". $row['shipping_zone_id'];

                $sub_sql_query_6 = "SELECT
                    oc_order_total.title as 'coupon_applied'
                    FROM oc_order_total
                    WHERE oc_order_total.order_id = ". $row['order_id'] . " AND oc_order_total.code = 'coupon';";

                $q_sub_1 = null;
                $q_sub_2 = null;
                $q_sub_5 = null;
                $q_sub_6 = null;
                try {
                    $q_sub_1 = $conn->query($sub_sql_query_1);
                    $q_sub_1->setFetchMode(PDO::FETCH_ASSOC);
                    $q_sub_2 = $conn->query($sub_sql_query_2);
                    $q_sub_2->setFetchMode(PDO::FETCH_ASSOC);
                    $q_sub_5 = $conn->query($sub_sql_query_5);
                    $q_sub_5->setFetchMode(PDO::FETCH_ASSOC);
                    $q_sub_6 = $conn->query($sub_sql_query_6);
                    $q_sub_6->setFetchMode(PDO::FETCH_ASSOC);
                } catch(PDOException $e) {
                    echo "Error: " . $e->getMessage();
                }

                $q_vals_sub_1 = $q_sub_1->fetchAll();
                $q_vals_sub_2 = $q_sub_2->fetchAll();
                $q_vals_sub_5 = $q_sub_2->fetchAll();
                $q_vals_sub_6 = $q_sub_6->fetchAll();

                $payment_state_name = "";
                if(count($q_vals_sub_2) != 0) {
                    foreach($q_vals_sub_2 as $sub_row_2) {
                        $payment_state_name = $sub_row_2['payment_state_name'];
                    }
                }

                $shipping_state_name = "";
                if(count($q_vals_sub_5) != 0) {
                    foreach($q_vals_sub_5 as $sub_row_5) {
                        $shipping_state_name = $sub_row_5['shipping_state_name'];
                    }
                }

                $coupon_applied = "";
                if(count($q_vals_sub_6) != 0) {
                    foreach($q_vals_sub_6 as $sub_row_6) {
                        $coupon_applied = $sub_row_6['coupon_applied'];
                    }
                }

                $objPHPExcel->getActiveSheet()->setCellValue('A'.$index,($index-1));
                $objPHPExcel->getActiveSheet()->setCellValue('B'.$index,addTimeBuffer($row['order_date']));
                $objPHPExcel->getActiveSheet()->setCellValue('C'.$index,$row['order_id']);
                $objPHPExcel->getActiveSheet()->setCellValue('D'.$index,$row['customer_id']);
                $objPHPExcel->getActiveSheet()->setCellValue('E'.$index,$row['contact_person']);

                if($row['contact_number'] == "" || $row['contact_number'] == null) {

                    $sub_sql_query_4 = "SELECT
                        oc_address.custom_field as 'custom_field'

                        FROM oc_address

                        WHERE oc_address.customer_id = ". $row['customer_id']; 

                    $q_sub_4 = null;
                    try {
                        $q_sub_4 = $conn->query($sub_sql_query_4);
                        $q_sub_4->setFetchMode(PDO::FETCH_ASSOC);
                    } catch(PDOException $e) {
                        echo "Error: " . $e->getMessage();
                    }

                    $q_vals_sub_4 = $q_sub_4->fetchAll();

                    $updated_number = "";

                    if(count($q_vals_sub_4) != 0) {
                        foreach($q_vals_sub_4 as $sub_row_4) {
                            $updated_number = json_decode($sub_row_4['custom_field'], true)['1'];
                        }
                    }
                    $objPHPExcel->getActiveSheet()->setCellValue('F'.$index,$updated_number);
                } else {
                    $objPHPExcel->getActiveSheet()->setCellValue('F'.$index,$row['contact_number']);
                }


                $objPHPExcel->getActiveSheet()->setCellValue('G'.$index,$row['contact_email']);
                if($row['payment_mode'] == "" || $row['payment_mode'] == null) {
                    $objPHPExcel->getActiveSheet()->setCellValue('J'.$index,"Online Payment");
                } else {
                    $objPHPExcel->getActiveSheet()->setCellValue('J'.$index,$row['payment_mode']);
                }
                $objPHPExcel->getActiveSheet()->setCellValue('K'.$index,$row['payment_total']);
                $objPHPExcel->getActiveSheet()->setCellValue('L'.$index,"");
                $objPHPExcel->getActiveSheet()->setCellValue('M'.$index,"");
                $objPHPExcel->getActiveSheet()->setCellValue('N'.$index,$row['payment_city']);
                if($payment_state_name == "" || $payment_state_name == null) {
                    $objPHPExcel->getActiveSheet()->setCellValue('O'.$index,$row['payment_city']);
                } else {
                    $objPHPExcel->getActiveSheet()->setCellValue('O'.$index,$payment_state_name);
                }
                $objPHPExcel->getActiveSheet()->setCellValue('P'.$index,$row['payment_address']);
                $objPHPExcel->getActiveSheet()->setCellValue('Q'.$index,$row['payment_postcode']);

                $objPHPExcel->getActiveSheet()->setCellValue('R'.$index,$row['shipping_city']);
                if($shipping_state_name == "" || $shipping_state_name == null) {
                    $objPHPExcel->getActiveSheet()->setCellValue('S'.$index,$row['shipping_city']);
                } else {
                    $objPHPExcel->getActiveSheet()->setCellValue('S'.$index,$shipping_state_name);
                }
                $objPHPExcel->getActiveSheet()->setCellValue('T'.$index,$row['shipping_address']);
                $objPHPExcel->getActiveSheet()->setCellValue('U'.$index,$row['shipping_postcode']);

                $objPHPExcel->getActiveSheet()->setCellValue('V'.$index,$coupon_applied);

                $col_index = 'X';
                $no_packets = 0;
                $grand_total_weight = 0;

                if(count($q_vals_sub_1) != 0) {
                    foreach($q_vals_sub_1 as $sub_row_1) {
                        $no_packets += $sub_row_1['product_quantity'];

                        $sub_sql_query_3 = "SELECT
                            oc_product.weight as 'product_weight',
                            oc_product.weight_class_id as 'product_weight_id'
                            FROM oc_product
                            WHERE oc_product.product_id = ". $sub_row_1['product_id']; 

                        $q_sub_3 = null;
                        try {
                            $q_sub_3 = $conn->query($sub_sql_query_3);
                            $q_sub_3->setFetchMode(PDO::FETCH_ASSOC);
                        } catch(PDOException $e) {
                            echo "Error: " . $e->getMessage();
                        }

                        $q_vals_sub_3 = $q_sub_3->fetchAll();

                        $item_weight = 0;
                        if(count($q_vals_sub_3) != 0) {
                            foreach($q_vals_sub_3 as $sub_row_3) {
                                if($sub_row_3['product_weight_id'] == 1) {
                                    $item_weight = $sub_row_3['product_weight'] * 1000;
                                } else if($sub_row_3['product_weight_id'] == 2) {
                                    $item_weight = $sub_row_3['product_weight'];
                                }
                            }
                        }
                        
                        $total_item_weight = $item_weight * $sub_row_1['product_quantity'];
                        $grand_total_weight += $total_item_weight;

                        while($sub_row_1['product_quantity']>=1) {
                            $objPHPExcel->getActiveSheet()->setCellValue($col_index.$index,$sub_row_1['product_name']);
                            $col_index++;
                            $sub_row_1['product_quantity']--;
                        }
                    }
                }

                $objPHPExcel->getActiveSheet()->setCellValue('I'.$index,$grand_total_weight);
                $objPHPExcel->getActiveSheet()->setCellValue('H'.$index,$no_packets);

                $index++;

            }

        }

    }

    getMissingOrders($conn, $objPHPExcel, $cell_header_style);
}

/* 
    ##################################################################      
        For Missing Orders
    ##################################################################
*/
function getMissingOrders($conn, $objPHPExcel, $cell_header_style) {

    global $actual_order_email;

    $sql_query = "SELECT
        oc_order.date_added as 'order_date',
        oc_order.order_id as  'order_id',
        oc_order.customer_id as 'customer_id',
        CONCAT(oc_order.firstname,' ',oc_order.lastname) as 'contact_person',
        oc_order.telephone as 'contact_number',
        oc_order.email as 'contact_email',
        oc_order.payment_method as 'payment_mode',
        oc_order.total as 'payment_total',
        oc_order.payment_city as 'payment_city',
        oc_order.payment_zone_id as 'payment_zone_id',
        CONCAT(oc_order.payment_address_1,' ',oc_order.payment_address_2) as 'payment_address',
        oc_order.payment_postcode as 'payment_postcode',
        oc_order.shipping_city as 'shipping_city',
        oc_order.shipping_zone_id as 'shipping_zone_id',
        CONCAT(oc_order.shipping_address_1,' ',oc_order.shipping_address_2) as 'shipping_address',
        oc_order.shipping_postcode as 'shipping_postcode'
        
        FROM oc_order

        INNER JOIN oc_order_product 
        ON oc_order_product.order_id = oc_order.order_id

        WHERE 
        oc_order.date_added BETWEEN '".subtractTimeBuffer($_POST['start_date'])."' AND '".subtractTimeBuffer($_POST['end_date'])."'
        
        AND oc_order.order_id NOT IN (SELECT order_id FROM oc_order_history) AND oc_order.order_id > 6610

        GROUP BY oc_order.order_id";

        //echo $sql_query;

        // Gets the data from the database
        $q = null;
        try {
            $q = $conn->query($sql_query);
            $q->setFetchMode(PDO::FETCH_ASSOC);
        } catch(PDOException $e) {
            echo "Error: " . $e->getMessage();
        }

        $q_vals = $q->fetchAll();

        $objPHPExcel->createSheet(1);
        $objPHPExcel->setActiveSheetIndex(1);
        $objPHPExcel->getActiveSheet()->setTitle("Missing Orders");

        for($ind = 'A'; $ind<='V'; $ind++) {
            $objPHPExcel->getActiveSheet()->getStyle($ind.'1')->applyFromArray($cell_header_style);
        }

        $objPHPExcel->getActiveSheet()->setCellValue('A1',"S. No.");
        $objPHPExcel->getActiveSheet()->setCellValue('B1',"Order Date"); 
        $objPHPExcel->getActiveSheet()->setCellValue('C1',"Order Id"); 
        $objPHPExcel->getActiveSheet()->setCellValue('D1',"Customer ID");
        $objPHPExcel->getActiveSheet()->setCellValue('E1',"Contact Person"); 
        $objPHPExcel->getActiveSheet()->setCellValue('F1',"Contact Number"); 
        $objPHPExcel->getActiveSheet()->setCellValue('G1',"Email ID");
        $objPHPExcel->getActiveSheet()->setCellValue('H1',"No. of Packets"); 
        $objPHPExcel->getActiveSheet()->setCellValue('I1',"Weights(gms)"); 
        $objPHPExcel->getActiveSheet()->setCellValue('J1',"Mode"); 
        $objPHPExcel->getActiveSheet()->setCellValue('K1',"Amount(Rs)"); 
        $objPHPExcel->getActiveSheet()->setCellValue('L1',"Courier"); 
        $objPHPExcel->getActiveSheet()->setCellValue('M1',"AWB Number");
        $objPHPExcel->getActiveSheet()->setCellValue('N1',"Billing City"); 
        $objPHPExcel->getActiveSheet()->setCellValue('O1',"Billing State");  
        $objPHPExcel->getActiveSheet()->setCellValue('P1',"Billing Address"); 
        $objPHPExcel->getActiveSheet()->setCellValue('Q1',"Billing Pin Code"); 
        $objPHPExcel->getActiveSheet()->setCellValue('R1',"Shipping City"); 
        $objPHPExcel->getActiveSheet()->setCellValue('S1',"Shipping State");  
        $objPHPExcel->getActiveSheet()->setCellValue('T1',"Shipping Address"); 
        $objPHPExcel->getActiveSheet()->setCellValue('U1',"Shipping Pin Code"); 
        $objPHPExcel->getActiveSheet()->setCellValue('V1',"Product Selection");

        $index = 2;

        if(count($q_vals) == 0) {
            echo "No Records Found";
            die();
        } else {

        foreach($q_vals as $row) {

            if(!in_array($row['contact_email'], $actual_order_email)) {

                $sub_sql_query_1 = "SELECT
                    oc_order_product.name as 'product_name',
                    oc_order_product.quantity as 'product_quantity',
                    oc_order_product.product_id as 'product_id'
                    FROM oc_order_product
                    WHERE oc_order_product.order_id = ". $row['order_id'];

                $sub_sql_query_2 = "SELECT
                    oc_zone.name as 'payment_state_name'
                    FROM oc_zone
                    WHERE oc_zone.zone_id = ". $row['payment_zone_id'];

                $sub_sql_query_5 = "SELECT
                    oc_zone.name as 'shipping_state_name'
                    FROM oc_zone
                    WHERE oc_zone.zone_id = ". $row['shipping_zone_id'];

                $q_sub_1 = null;
                $q_sub_2 = null;
                $q_sub_5 = null;
                try {
                    $q_sub_1 = $conn->query($sub_sql_query_1);
                    $q_sub_1->setFetchMode(PDO::FETCH_ASSOC);
                    $q_sub_2 = $conn->query($sub_sql_query_2);
                    $q_sub_2->setFetchMode(PDO::FETCH_ASSOC);
                    $q_sub_5 = $conn->query($sub_sql_query_5);
                    $q_sub_5->setFetchMode(PDO::FETCH_ASSOC);
                } catch(PDOException $e) {
                    echo "Error: " . $e->getMessage();
                }

                $q_vals_sub_1 = $q_sub_1->fetchAll();
                $q_vals_sub_2 = $q_sub_2->fetchAll();
                $q_vals_sub_5 = $q_sub_2->fetchAll();

                $payment_state_name = "";
                if(count($q_vals_sub_2) != 0) {
                    foreach($q_vals_sub_2 as $sub_row_2) {
                        $payment_state_name = $sub_row_2['payment_state_name'];
                    }
                }

                $shipping_state_name = "";
                if(count($q_vals_sub_5) != 0) {
                    foreach($q_vals_sub_5 as $sub_row_5) {
                        $shipping_state_name = $sub_row_5['shipping_state_name'];
                    }
                }

                $objPHPExcel->getActiveSheet()->setCellValue('A'.$index,($index-1));
                $objPHPExcel->getActiveSheet()->setCellValue('B'.$index,addTimeBuffer($row['order_date']));
                $objPHPExcel->getActiveSheet()->setCellValue('C'.$index,$row['order_id']);
                $objPHPExcel->getActiveSheet()->setCellValue('D'.$index,$row['customer_id']);
                $objPHPExcel->getActiveSheet()->setCellValue('E'.$index,$row['contact_person']);

                if($row['contact_number'] == "" || $row['contact_number'] == null) {

                    $sub_sql_query_4 = "SELECT
                        oc_address.custom_field as 'custom_field'
                        FROM oc_address
                        WHERE oc_address.customer_id = ". $row['customer_id']; 

                    $q_sub_4 = null;
                    try {
                        $q_sub_4 = $conn->query($sub_sql_query_4);
                        $q_sub_4->setFetchMode(PDO::FETCH_ASSOC);
                    } catch(PDOException $e) {
                        echo "Error: " . $e->getMessage();
                    }

                    $q_vals_sub_4 = $q_sub_4->fetchAll();

                    $updated_number = "";

                    if(count($q_vals_sub_4) != 0) {
                        foreach($q_vals_sub_4 as $sub_row_4) {
                            $updated_number = json_decode($sub_row_4['custom_field'], true)['1'];
                        }
                    }
                    $objPHPExcel->getActiveSheet()->setCellValue('F'.$index,$updated_number);
                } else {
                    $objPHPExcel->getActiveSheet()->setCellValue('F'.$index,$row['contact_number']);
                }


                $objPHPExcel->getActiveSheet()->setCellValue('G'.$index,$row['contact_email']);
                if($row['payment_mode'] == "" || $row['payment_mode'] == null) {
                    $objPHPExcel->getActiveSheet()->setCellValue('J'.$index,"Online Payment");
                } else {
                    $objPHPExcel->getActiveSheet()->setCellValue('J'.$index,$row['payment_mode']);
                }
                $objPHPExcel->getActiveSheet()->setCellValue('K'.$index,$row['payment_total']);
                $objPHPExcel->getActiveSheet()->setCellValue('L'.$index,"");
                $objPHPExcel->getActiveSheet()->setCellValue('M'.$index,"");
                $objPHPExcel->getActiveSheet()->setCellValue('N'.$index,$row['payment_city']);
                if($payment_state_name == "" || $payment_state_name == null) {
                    $objPHPExcel->getActiveSheet()->setCellValue('O'.$index,$row['payment_city']);
                } else {
                    $objPHPExcel->getActiveSheet()->setCellValue('O'.$index,$payment_state_name);
                }
                $objPHPExcel->getActiveSheet()->setCellValue('P'.$index,$row['payment_address']);
                $objPHPExcel->getActiveSheet()->setCellValue('Q'.$index,$row['payment_postcode']);

                $objPHPExcel->getActiveSheet()->setCellValue('R'.$index,$row['shipping_city']);
                if($shipping_state_name == "" || $shipping_state_name == null) {
                    $objPHPExcel->getActiveSheet()->setCellValue('S'.$index,$row['shipping_city']);
                } else {
                    $objPHPExcel->getActiveSheet()->setCellValue('S'.$index,$shipping_state_name);
                }
                $objPHPExcel->getActiveSheet()->setCellValue('T'.$index,$row['shipping_address']);
                $objPHPExcel->getActiveSheet()->setCellValue('U'.$index,$row['shipping_postcode']);

                $col_index = 'V';
                $no_packets = 0;
                $grand_total_weight = 0;

                if(count($q_vals_sub_1) != 0) {
                    foreach($q_vals_sub_1 as $sub_row_1) {
                        $no_packets += $sub_row_1['product_quantity'];

                        $sub_sql_query_3 = "SELECT
                            oc_product.weight as 'product_weight',
                            oc_product.weight_class_id as 'product_weight_id'
                            FROM oc_product
                            WHERE oc_product.product_id = ". $sub_row_1['product_id']; 

                        $q_sub_3 = null;
                        try {
                            $q_sub_3 = $conn->query($sub_sql_query_3);
                            $q_sub_3->setFetchMode(PDO::FETCH_ASSOC);
                        } catch(PDOException $e) {
                            echo "Error: " . $e->getMessage();
                        }

                        $q_vals_sub_3 = $q_sub_3->fetchAll();

                        $item_weight = 0;
                        if(count($q_vals_sub_3) != 0) {
                            foreach($q_vals_sub_3 as $sub_row_3) {
                                if($sub_row_3['product_weight_id'] == 1) {
                                    $item_weight = $sub_row_3['product_weight'] * 1000;
                                } else if($sub_row_3['product_weight_id'] == 2) {
                                    $item_weight = $sub_row_3['product_weight'];
                                }
                            }
                        }
                        
                        $total_item_weight = $item_weight * $sub_row_1['product_quantity'];
                        $grand_total_weight += $total_item_weight;

                        while($sub_row_1['product_quantity']>=1) {
                            $objPHPExcel->getActiveSheet()->setCellValue($col_index.$index,$sub_row_1['product_name']);
                            $col_index++;
                            $sub_row_1['product_quantity']--;
                        }
                    }
                }

                $objPHPExcel->getActiveSheet()->setCellValue('I'.$index,$grand_total_weight);
                $objPHPExcel->getActiveSheet()->setCellValue('H'.$index,$no_packets);

                $index++;

            }

        }

    }

    getAbandonCart($conn, $objPHPExcel, $cell_header_style);
}

/* 
    ##################################################################      
        For Abandoned Cart Orders
    ##################################################################
*/
function getAbandonCart($conn, $objPHPExcel, $cell_header_style) {
    
    global $actual_order_email;

    $sql_query = "SELECT
        oc_cart.customer_id as 'customer_id',
        max(oc_cart.date_added) as 'latest_date'
        FROM oc_cart

        WHERE 
        oc_cart.date_added BETWEEN '".subtractTimeBuffer($_POST['start_date'])."' AND '".subtractTimeBuffer($_POST['end_date'])."'
        
        GROUP BY oc_cart.customer_id;
        ";

        //echo $sql_query;

        // Gets the data from the database
        $q = null;
        try {
            $q = $conn->query($sql_query);
            $q->setFetchMode(PDO::FETCH_ASSOC);
        } catch(PDOException $e) {
            echo "Error: " . $e->getMessage();
        }

        $q_vals = $q->fetchAll();

        $objPHPExcel->createSheet(1);
        $objPHPExcel->setActiveSheetIndex(1);
        $objPHPExcel->getActiveSheet()->setTitle("Abandoned Cart");

        for($ind = 'A'; $ind<='E'; $ind++) {
            $objPHPExcel->getActiveSheet()->getStyle($ind.'1')->applyFromArray($cell_header_style);
        }

        $objPHPExcel->getActiveSheet()->setCellValue('A1',"S. No.");
        $objPHPExcel->getActiveSheet()->setCellValue('B1',"Date Added");
        $objPHPExcel->getActiveSheet()->setCellValue('C1',"Customer ID"); 
        $objPHPExcel->getActiveSheet()->setCellValue('D1',"Customer Name"); 
        $objPHPExcel->getActiveSheet()->setCellValue('E1',"Customer Email");
        
        $index = 2;

        if(count($q_vals) == 0) {
            echo "No Records Found";
            die();
        } else {

        foreach($q_vals as $row) {

            if(true) {

                $sub_sql_query_1 = "SELECT
                    CONCAT(oc_customer.firstname, ' ', oc_customer.lastname) as 'customer_name',
                    oc_customer.email as 'customer_email'
                    
                    FROM oc_customer
                    WHERE oc_customer.customer_id = ". $row['customer_id'];

                $q_sub_1 = null;
                try {
                    $q_sub_1 = $conn->query($sub_sql_query_1);
                    $q_sub_1->setFetchMode(PDO::FETCH_ASSOC);
                } catch(PDOException $e) {
                    echo "Error: " . $e->getMessage();
                }

                $q_vals_sub_1 = $q_sub_1->fetchAll();

                if(isset($q_vals_sub_1[0]) && $q_vals_sub_1[0]!=null) {
                    $objPHPExcel->getActiveSheet()->setCellValue('A'.$index,($index-1));
                    $objPHPExcel->getActiveSheet()->setCellValue('B'.$index,addTimeBuffer($row['latest_date']));
                    $objPHPExcel->getActiveSheet()->setCellValue('C'.$index,$row['customer_id']);
                    $objPHPExcel->getActiveSheet()->setCellValue('D'.$index,$q_vals_sub_1[0]['customer_name']);
                    $objPHPExcel->getActiveSheet()->setCellValue('E'.$index,$q_vals_sub_1[0]['customer_email']);
    
                    $index++;
                }

            }

        }

    }
}

?>