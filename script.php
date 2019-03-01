<?php
	// DB
	$servername = "localhost";
	$username = "username";
	$password = "password";
	$dbname = "dbname";
	// Prefix for DB (do not touch if no prefix)
	$prefix14 = "";
	$prefix23 = "";

	// Create connection
	$conn = new mysqli($servername, $username, $password, $dbname);

	// Check connection
	if ($conn->connect_error) {
	    die("Connection failed: " . $conn->connect_error);
	}
	// Set UTF8
	$conn->query("SET NAMES 'utf8'");

	// output headers so that the file is downloaded rather than displayed
	header('Content-Type: text/csv; charset=utf-8');
	header('Content-Disposition: attachment; filename=migrationScript.sql');

	// create a file pointer connected to the output stream
	$output = fopen('php://output', 'w');

	/*************************/
	/* CREATE MISSING TABLES */
	/*************************/

	// Create `option`
	$sqlCreate = "CREATE TABLE `".$prefix14."option` (`option_id` INT (11) AUTO_INCREMENT NOT NULL, `type` VARCHAR (32) NOT NULL, `sort_order` INT (3), PRIMARY KEY (`option_id`))";
	if ($conn->query($sqlCreate) === TRUE) {
	    $sql = "SELECT po.product_option_id, name, po.sort_order FROM ".$prefix14."product_option po JOIN ".$prefix14."product_option_description pod ON po.product_option_id = pod.product_option_id WHERE pod.language_id = 2";
	    $result = $conn->query($sql);
	    if ($result->num_rows > 0) {
	        // output data of each row
	        while($row = $result->fetch_assoc()) {
	        	if ($row["name"] == "type" || $row["name"] == "Type") {
	        		$type = "select";
	        	} else {
	        		$type = $row["name"];
	        	}

	        	$sqlInsert = 'INSERT INTO `'.$prefix14.'option` (`option_id`, `type`, `sort_order`) VALUES ('.$row["product_option_id"].',"'.addslashes($type).'",'.$row["sort_order"].');';
	        	if ($conn->query($sqlInsert) === FALSE) {
	        	    exit("Error inserting row: $conn->error");
	        	}
	        }
	    } else {
	        exit("0 results for $sqlCreate");
	    }
	} else {
	    exit("Error: $conn->error");
	}

	// Create `option_description`
	$sqlCreate = "CREATE TABLE `".$prefix14."option_description` (`option_id` INT (11) AUTO_INCREMENT NOT NULL, `language_id` INT (11) NOT NULL, `name` VARCHAR (128), PRIMARY KEY (`option_id`, `language_id`))";
	if ($conn->query($sqlCreate) === TRUE) {
	    $sql = "SELECT * FROM ".$prefix14."product_option_description";
	    $result = $conn->query($sql);
	    if ($result->num_rows > 0) {
	        // output data of each row
	        while($row = $result->fetch_assoc()) {
	        	$sqlInsert = 'INSERT INTO `'.$prefix14.'option_description` (`option_id`, `language_id`, `name`) VALUES ('.$row["product_option_id"].','.$row["language_id"].',"'.addslashes($row["name"]).'");';
	        	if ($conn->query($sqlInsert) === FALSE) {
	        	    exit("Error inserting row: $conn->error");
	        	}
	        }
	    } else {
	        exit("0 results for $sqlCreate");
	    }
	} else {
	    exit("Error: $conn->error");
	}

	// Create `option_value`
	$sqlCreate = "CREATE TABLE `".$prefix14."option_value` (`option_value_id` INT (11) AUTO_INCREMENT NOT NULL,`option_id` INT (11) NOT NULL, `sort_order` INT (3) NOT NULL, PRIMARY KEY (`option_value_id`))";
		if ($conn->query($sqlCreate) === TRUE) {
		    $sql = "SELECT * FROM ".$prefix14."product_option_value";
		    $result = $conn->query($sql);
		    if ($result->num_rows > 0) {
		        // output data of each row
		        while($row = $result->fetch_assoc()) {
		        	$sqlInsert = 'INSERT INTO `'.$prefix14.'option_value` (`option_value_id`, `option_id`, `sort_order`) VALUES ('.$row["product_option_value_id"].','.$row["product_option_id"].','.$row["sort_order"].');';
		        	if ($conn->query($sqlInsert) === FALSE) {
		        	    exit("Error inserting row: $conn->error");
		        	}
		        }
		    } else {
		        exit("0 results for $sqlCreate");
		    }
		} else {
		    exit("Error: $conn->error");
		}

	// Create `option_value`
	$sqlCreate = "CREATE TABLE `".$prefix14."option_value_description` (`option_value_id` INT (11) AUTO_INCREMENT NOT NULL,`language_id` INT (11) NOT NULL, `option_id` INT (11) NOT NULL, `name` VARCHAR (128), PRIMARY KEY (`option_value_id`, `language_id`))";
	if ($conn->query($sqlCreate) === TRUE) {
	    $sql = "SELECT povd.product_option_value_id, language_id, pov.product_option_id, povd.name FROM ".$prefix14."product_option_value pov JOIN ".$prefix14."product_option_value_description povd ON pov.product_option_value_id = povd.product_option_value_id";
	    $result = $conn->query($sql);
	    if ($result->num_rows > 0) {
	        // output data of each row
	        while($row = $result->fetch_assoc()) {
	        	$sqlInsert = 'INSERT INTO `'.$prefix14.'option_value_description` (`option_value_id`, `language_id`, `option_id`, `name`) VALUES ('.$row["product_option_value_id"].','.$row["language_id"].','.$row["product_option_id"].',"'.addslashes($row["name"]).'");';
	        	if ($conn->query($sqlInsert) === FALSE) {
	        	    exit("Error inserting row: $conn->error");
	        	}
	        }
	    } else {
	        exit("0 results for $sqlCreate");
	    }
	} else {
	    exit("Error: $conn->error");
	}

	/******************/
	/* EXTRACT TABLES */
	/******************/

	// Extract `address`
	$sql = "SELECT * FROM `".$prefix14."address`";
	$result = $conn->query($sql);

	if ($result->num_rows > 0) {
		$line = 'INSERT INTO `'.$prefix23.'address` (`address_id`, `customer_id`, `firstname`, `lastname`, `company`, `address_1`, `address_2`, `city`, `postcode`, `country_id`, `zone_id`) VALUES ';
	    // output data of each row
	    while($row = $result->fetch_assoc()) {
	    	$line .= '('.$row["address_id"].','.$row["customer_id"].',"'.addslashes($row["firstname"]).'","'.addslashes($row["lastname"]).'","'.addslashes($row["company"]).'","'.addslashes($row["address_1"]).'","'.addslashes($row["address_2"]).'","'.addslashes($row["city"]).'","'.addslashes($row["postcode"]).'",'.$row["country_id"].','.$row["zone_id"].'),';
	    }
	    // remove last comma
	    $line = substr($line, 0, -1);
	    $line .= ';'.PHP_EOL.PHP_EOL;
	} else {
	    exit("0 results for $sql");
	}

	// Extract `category`
	$sql = "SELECT * FROM `".$prefix14."category`";
	$result = $conn->query($sql);

	if ($result->num_rows > 0) {
		$line .= 'INSERT INTO `'.$prefix23.'category` (`category_id`, `image`, `parent_id`, `top`, `column`, `sort_order`, `status`, `date_added`, `date_modified`) VALUES ';
	    // output data of each row
	    while($row = $result->fetch_assoc()) {
	    	$line .= '('.$row["category_id"].',"'.addslashes($row["image"]).'",'.$row["parent_id"].',0,0,'.$row["sort_order"].','.$row["status"].',"'.addslashes($row["date_added"]).'","'.addslashes($row["date_modified"]).'"),';
	    }
	    // remove last comma
	    $line = substr($line, 0, -1);
	    $line .= ';'.PHP_EOL.PHP_EOL;
	} else {
	    exit("0 results for $sql");
	}

	// Extract `category_description`
	$sql = "SELECT * FROM `".$prefix14."category_description`";
	$result = $conn->query($sql);

	if ($result->num_rows > 0) {
		$line .= 'INSERT INTO `'.$prefix23.'category_description` (`category_id`, `language_id`, `name`, `description`, `meta_title`, `meta_description`, `meta_keyword`) VALUES ';
	    // output data of each row
	    while($row = $result->fetch_assoc()) {
	    	$line .= '('.$row["category_id"].','.$row["language_id"].',"'.addslashes($row["name"]).'","'.addslashes($row["description"]).'","'.addslashes($row["name"]).'","'.addslashes($row["meta_description"]).'","'.addslashes($row["meta_keywords"]).'"),';
	    }
	    // remove last comma
	    $line = substr($line, 0, -1);
	    $line .= ';'.PHP_EOL.PHP_EOL;
	} else {
	    exit("0 results for $sql");
	}

	// Extract `category_path`
	$sql = "SELECT `category_id` FROM `".$prefix14."category`";
	$result = $conn->query($sql);

	if ($result->num_rows > 0) {
		$line .= 'INSERT INTO `'.$prefix23.'category_path` (`category_id`, `path_id`, `level`) VALUES ';
	    // output data of each row
	    while($row = $result->fetch_assoc()) {
	    	$line .= '('.$row["category_id"].','.$row["category_id"].',0),';
	    }
	    // remove last comma
	    $line = substr($line, 0, -1);
	    $line .= ';'.PHP_EOL.PHP_EOL;
	} else {
	    exit("0 results for $sql");
	}

	// Extract `category_to_layout`
	$sql = "SELECT `category_id` FROM `".$prefix14."category`";
	$result = $conn->query($sql);

	if ($result->num_rows > 0) {
		$line .= 'INSERT INTO `'.$prefix23.'category_to_layout` (`category_id`, `store_id`, `layout_id`) VALUES ';
	    // output data of each row
	    while($row = $result->fetch_assoc()) {
	    	$line .= '('.$row["category_id"].',0,0),';
	    }
	    // remove last comma
	    $line = substr($line, 0, -1);
	    $line .= ';'.PHP_EOL.PHP_EOL;
	} else {
	    exit("0 results for $sql");
	}

	// Extract `category_to_store`
	$sql = "SELECT * FROM `".$prefix14."category_to_store`";
	$result = $conn->query($sql);

	if ($result->num_rows > 0) {
		$line .= 'INSERT INTO `'.$prefix23.'category_to_store` (`category_id`, `store_id`) VALUES ';
	    // output data of each row
	    while($row = $result->fetch_assoc()) {
	    	$line .= '('.$row["category_id"].','.$row["store_id"].'),';
	    }
	    // remove last comma
	    $line = substr($line, 0, -1);
	    $line .= ';'.PHP_EOL.PHP_EOL;
	} else {
	    exit("0 results for $sql");
	}

	// Extract `customer`
	$sql = "SELECT * FROM `".$prefix14."customer`";
	$result = $conn->query($sql);

	if ($result->num_rows > 0) {
		$line .= 'INSERT INTO `'.$prefix23.'customer` (`customer_id`, `customer_group_id`, `store_id`, `language_id`, `firstname`, `lastname`, `email`, `telephone`, `fax`, `password`, `salt`, `newsletter`, `address_id`, `ip`, `status`, `approved`, `safe`, `token`, `code`, `date_added`) VALUES ';
	    // output data of each row
	    while($row = $result->fetch_assoc()) {
	    	// Concatenate phone numbers
	    	if($row["telephone"]) {
	    		if($row["gsm"]) {
	    			$tel = addslashes($row["telephone"])." - ".addslashes($row["gsm"]);
	    		} else {
	    			$tel = addslashes($row["telephone"]);
	    		}
	    	} else if($row["gsm"]) {
	    		$tel = addslashes($row["gsm"]);
	    	}

	    	$line .= '('.$row["customer_id"].',1,0,2,"'.addslashes($row["firstname"]).'","'.addslashes($row["lastname"]).'","'.addslashes($row["email"]).'","'.$tel.'","'.addslashes($row["fax"]).'","'.addslashes($row["password"]).'","kHcFmd8i7",'.addslashes($row["newsletter"]).','.$row["address_id"].',"'.$row["ip"].'",'.$row["status"].','.$row["approved"].',0,"","","'.$row["date_added"].'"),';
	    }
	    // remove last comma
	    $line = substr($line, 0, -1);
	    $line .= ';'.PHP_EOL.PHP_EOL;
	} else {
	    exit("0 results for $sql");
	}

	// Extract `option`
	$sql = "SELECT * FROM `".$prefix14."option`";
	$result = $conn->query($sql);

	if ($result->num_rows > 0) {
		$line .= 'INSERT INTO `'.$prefix23.'option` (`option_id`, `type`, `sort_order`) VALUES ';
	    // output data of each row
	    while($row = $result->fetch_assoc()) {
	    	$line .= '('.$row["option_id"].',"'.addslashes($row["type"]).'",'.$row["sort_order"].'),';
	    }
	    // remove last comma
	    $line = substr($line, 0, -1);
	    $line .= ';'.PHP_EOL.PHP_EOL;
	} else {
	    exit("0 results for $sql");
	}

	// Extract `option_description`
	$sql = "SELECT * FROM `".$prefix14."option_description`";
	$result = $conn->query($sql);

	if ($result->num_rows > 0) {
		$line .= 'INSERT INTO `'.$prefix23.'option_description` (`option_id`, `language_id`, `name`) VALUES ';
	    // output data of each row
	    while($row = $result->fetch_assoc()) {
	    	$line .= '('.$row["option_id"].','.$row["language_id"].',"'.addslashes($row["name"]).'"),';
	    }
	    // remove last comma
	    $line = substr($line, 0, -1);
	    $line .= ';'.PHP_EOL.PHP_EOL;
	} else {
	    exit("0 results for $sql");
	}

	// Extract `option_value`
	$sql = "SELECT * FROM `".$prefix14."option_value`";
	$result = $conn->query($sql);

	if ($result->num_rows > 0) {
		$line .= 'INSERT INTO `'.$prefix23.'option_value` (`option_value_id`, `option_id`, `sort_order`) VALUES ';
	    // output data of each row
	    while($row = $result->fetch_assoc()) {
	    	$line .= '('.$row["option_value_id"].','.$row["option_id"].','.$row["sort_order"].'),';
	    }
	    // remove last comma
	    $line = substr($line, 0, -1);
	    $line .= ';'.PHP_EOL.PHP_EOL;
	} else {
	    exit("0 results for $sql");
	}

	// Extract `option_value_description`
	$sql = "SELECT * FROM `".$prefix14."option_value_description`";
	$result = $conn->query($sql);

	if ($result->num_rows > 0) {
		$line .= 'INSERT INTO `'.$prefix23.'option_value_description` (`option_value_id`, `language_id`, `option_id`, `name`) VALUES ';
	    // output data of each row
	    while($row = $result->fetch_assoc()) {
	    	$line .= '('.$row["option_value_id"].','.$row["language_id"].','.$row["option_id"].',"'.addslashes($row["name"]).'"),';
	    }
	    // remove last comma
	    $line = substr($line, 0, -1);
	    $line .= ';'.PHP_EOL.PHP_EOL;
	} else {
	    exit("0 results for $sql");
	}

	// Extract `order`
	$sql = "SELECT * FROM `".$prefix14."order`";
	$result = $conn->query($sql);

	if ($result->num_rows > 0) {
		$line .= 'INSERT INTO `'.$prefix23.'order` (`order_id`, `invoice_no`, `invoice_prefix`, `store_id`, `store_name`, `store_url`, `customer_id`, `customer_group_id`, `firstname`, `lastname`, `email`, `telephone`, `fax`, `payment_firstname`, `payment_lastname`, `payment_company`, `payment_address_1`, `payment_address_2`, `payment_city`, `payment_postcode`, `payment_country`, `payment_country_id`, `payment_zone`, `payment_zone_id`, `payment_address_format`, `payment_custom_field`, `payment_method`, `payment_code`, `shipping_firstname`, `shipping_lastname`, `shipping_company`, `shipping_address_1`, `shipping_address_2`, `shipping_city`, `shipping_postcode`, `shipping_country`, `shipping_country_id`, `shipping_zone`, `shipping_zone_id`, `shipping_address_format`, `shipping_custom_field`, `shipping_method`, `shipping_code`, `comment`, `total`, `order_status_id`, `affiliate_id`, `commission`, `marketing_id`, `language_id`, `currency_id`, `currency_code`, `currency_value`, `ip`, `date_added`, `date_modified`) VALUES ';
	    // output data of each row
	    while($row = $result->fetch_assoc()) {
	    	$line .= '('.$row["order_id"].','.$row["invoice_id"].',"'.addslashes($row["invoice_prefix"]).'",'.$row["store_id"].',"'.addslashes($row["store_name"]).'","'.addslashes($row["store_url"]).'",'.$row["customer_id"].','.$row["customer_group_id"].',"'.addslashes($row["firstname"]).'","'.addslashes($row["lastname"]).'","'.addslashes($row["email"]).'","'.addslashes($row["telephone"]).' - '.addslashes($row["gsm"]).'","'.addslashes($row["fax"]).'","'.addslashes($row["payment_firstname"]).'","'.addslashes($row["payment_lastname"]).'","'.addslashes($row["payment_company"]).'","'.addslashes($row["payment_address_1"]).'","'.addslashes($row["payment_address_2"]).'","'.addslashes($row["payment_city"]).'","'.addslashes($row["payment_postcode"]).'","'.addslashes($row["payment_country"]).'",'.$row["payment_country_id"].',"'.addslashes($row["payment_zone"]).'",'.$row["payment_zone_id"].',"'.addslashes($row["payment_address_format"]).'","[]","'.addslashes($row["payment_method"]).'","","'.addslashes($row["shipping_firstname"]).'","'.addslashes($row["shipping_lastname"]).'","'.addslashes($row["shipping_company"]).'","'.addslashes($row["shipping_address_1"]).'","'.addslashes($row["shipping_address_2"]).'","'.addslashes($row["shipping_city"]).'","'.addslashes($row["shipping_postcode"]).'","'.addslashes($row["shipping_country"]).'",'.$row["shipping_country_id"].',"'.addslashes($row["shipping_zone"]).'",'.$row["shipping_zone_id"].',"'.addslashes($row["shipping_address_format"]).'","[]","'.addslashes($row["shipping_method"]).'","","'.addslashes($row["comment"]).'",'.$row["total"].','.$row["order_status_id"].',0,0,0,'.$row["language_id"].','.$row["currency_id"].',"EUR",1,"'.addslashes($row["ip"]).'","'.addslashes($row["date_added"]).'","'.addslashes($row["date_modified"]).'"),';
	    }
	    // remove last comma
	    $line = substr($line, 0, -1);
	    $line .= ';'.PHP_EOL.PHP_EOL;
	} else {
	    exit("0 results for $sql");
	}

	// Extract `order_history`
	$sql = "SELECT * FROM `".$prefix14."order_history`";
	$result = $conn->query($sql);

	if ($result->num_rows > 0) {
		$line .= 'INSERT INTO `'.$prefix23.'order_history` (`order_history_id`, `order_id`, `order_status_id`, `notify`, `comment`, `date_added`) VALUES ';
	    // output data of each row
	    while($row = $result->fetch_assoc()) {
	    	$line .= '('.$row["order_history_id"].','.$row["order_id"].','.$row["order_status_id"].','.$row["notify"].',"'.addslashes($row["comment"]).'","'.addslashes($row["date_added"]).'"),';
	    }
	    // remove last comma
	    $line = substr($line, 0, -1);
	    $line .= ';'.PHP_EOL.PHP_EOL;
	} else {
	    exit("0 results for $sql");
	}

	// Extract `order_option`
	$sql = "SELECT * FROM `".$prefix14."order_option` oo JOIN `".$prefix14."product_option_value` pov ON oo.product_option_value_id = pov.product_option_value_id JOIN `".$prefix14."option` op ON pov.product_option_id = op.option_id";
	$result = $conn->query($sql);

	if ($result->num_rows > 0) {
		$line .= 'INSERT INTO `'.$prefix23.'order_option` (`order_option_id`, `order_id`, `order_product_id`, `product_option_id`, `product_option_value_id`, `name`, `value`, `type`) VALUES ';
	    // output data of each row
	    while($row = $result->fetch_assoc()) {
	    	$line .= '('.$row["order_option_id"].','.$row["order_id"].','.$row["order_product_id"].','.$row["product_option_id"].','.$row["product_option_value_id"].',"'.addslashes($row["name"]).'","'.addslashes($row["value"]).'","'.addslashes($row["type"]).'"),';
	    }
	    // remove last comma
	    $line = substr($line, 0, -1);
	    $line .= ';'.PHP_EOL.PHP_EOL;
	} else {
	    exit("0 results for $sql");
	}

	// Extract `order_product`
	$sql = "SELECT * FROM `".$prefix14."order_product`";
	$result = $conn->query($sql);

	if ($result->num_rows > 0) {
		$line .= 'INSERT INTO `'.$prefix23.'order_product` (`order_product_id`, `order_id`, `product_id`, `name`, `model`, `quantity`, `price`, `total`, `tax`, `reward`) VALUES ';
	    // output data of each row
	    while($row = $result->fetch_assoc()) {
	    	$line .= '('.$row["order_product_id"].','.$row["order_id"].','.$row["product_id"].',"'.addslashes($row["name"]).'","'.addslashes($row["model"]).'",'.$row["quantity"].','.$row["price"].','.$row["total"].','.$row["tax"].',0),';
	    }
	    // remove last comma
	    $line = substr($line, 0, -1);
	    $line .= ';'.PHP_EOL.PHP_EOL;
	} else {
	    exit("0 results for $sql");
	}

	// Extract `order_total`
	$sql = "SELECT * FROM `".$prefix14."order_total`";
	$result = $conn->query($sql);

	if ($result->num_rows > 0) {
		$line .= 'INSERT INTO `'.$prefix23.'order_total` (`order_total_id`, `order_id`, `code`, `title`, `value`, `sort_order`) VALUES ';
	    // output data of each row
	    while($row = $result->fetch_assoc()) {
	    	switch($row["title"]) {
	    		case 'Sous-total :':
	    			$totalCode = "sub_total";
	    			break;
	    		case 'Total :':
	    			$totalCode = "total";
	    			break;
	    		default:
	    			$totalCode = "shipping";
	    			break;
	    	}
	    	$line .= '('.$row["order_total_id"].','.$row["order_id"].',"'.$totalCode.'","'.addslashes($row["title"]).'",'.$row["value"].','.$row["sort_order"].'),';
	    }
	    // remove last comma
	    $line = substr($line, 0, -1);
	    $line .= ';'.PHP_EOL.PHP_EOL;
	} else {
	    exit("0 results for $sql");
	}

	// Extract `product`
	$sql = "SELECT * FROM `".$prefix14."product`";
	$result = $conn->query($sql);

	if ($result->num_rows > 0) {
		$line .= 'INSERT INTO `'.$prefix23.'product` (`product_id`, `model`, `sku`, `location`, `quantity`, `stock_status_id`, `image`, `manufacturer_id`, `shipping`, `price`, `tax_class_id`, `date_available`, `weight`, `weight_class_id`, `length`, `width`, `height`, `length_class_id`, `subtract`, `minimum`, `sort_order`, `status`, `viewed`, `date_added`, `date_modified`) VALUES ';
	    // output data of each row
	    while($row = $result->fetch_assoc()) {
	    	$line .= '('.$row["product_id"].',"'.addslashes($row["model"]).'","'.addslashes($row["sku"]).'","'.addslashes($row["location"]).'",'.$row["quantity"].','.$row["stock_status_id"].',"'.addslashes($row["image"]).'",'.$row["manufacturer_id"].','.$row["shipping"].',"'.$row["price"].'",'.$row["tax_class_id"].',"'.$row["date_available"].'","'.$row["weight"].'",'.$row["weight_class_id"].',"'.$row["length"].'","'.$row["width"].'","'.$row["height"].'",'.$row["length_class_id"].','.$row["subtract"].','.$row["minimum"].','.$row["sort_order"].','.$row["status"].','.$row["viewed"].',"'.$row["date_added"].'","'.$row["date_modified"].'"),';
	    }
	    // remove last comma
	    $line = substr($line, 0, -1);
	    $line .= ';'.PHP_EOL.PHP_EOL;
	} else {
	    exit("0 results for $sql");
	}

	// Extract `product_description`
	$sql = "SELECT * FROM `".$prefix14."product_description`";
	$result = $conn->query($sql);

	if ($result->num_rows > 0) {
		$line .= 'INSERT INTO `'.$prefix23.'product_description` (`product_id`, `language_id`, `name`, `description`, `meta_title`, `meta_description`, `meta_keyword`) VALUES ';
	    // output data of each row
	    while($row = $result->fetch_assoc()) {
	    	$line .= '('.$row["product_id"].','.$row["language_id"].',"'.addslashes($row["name"]).'","'.addslashes($row["description"]).'","'.addslashes($row["name"]).'","'.addslashes($row["meta_description"]).'","'.addslashes($row["meta_keywords"]).'"),';
	    }
	    // remove last comma
	    $line = substr($line, 0, -1);
	    $line .= ';'.PHP_EOL.PHP_EOL;
	} else {
	    exit("0 results for $sql");
	}

	// Extract `product_image`
	$sql = "SELECT * FROM `".$prefix14."product_image`";
	$result = $conn->query($sql);

	if ($result->num_rows > 0) {
	    // output data of each row
	    $line .= 'INSERT INTO `'.$prefix23.'product_image` (`product_image_id`, `product_id`, `image`) VALUES ';
	    while($row = $result->fetch_assoc()) {
	    	$line .= '('.$row["product_image_id"].','.$row["product_id"].',"'.addslashes($row["image"]).'"),';
	    }
	    // remove last comma
	    $line = substr($line, 0, -1);
	    $line .= ';'.PHP_EOL.PHP_EOL;
	} else {
	    exit("0 results for $sql");
	}

	// Extract `product_option`
	$sql = "SELECT * FROM `".$prefix14."product_option`";
	$result = $conn->query($sql);

	if ($result->num_rows > 0) {
		$line .= 'INSERT INTO `'.$prefix23.'product_option` (`product_option_id`, `product_id`, `option_id`, `value`, `required`) VALUES ';
	    // output data of each row
	    while($row = $result->fetch_assoc()) {
	    	$line .= '('.$row["product_option_id"].','.$row["product_id"].','.$row["product_option_id"].',"",1),';
	    }
	    // remove last comma
	    $line = substr($line, 0, -1);
	    $line .= ';'.PHP_EOL.PHP_EOL;
	} else {
	    exit("0 results for $sql");
	}

	// Extract `product_option_value`
	$sql = "SELECT * FROM `".$prefix14."product_option_value`";
	$result = $conn->query($sql);

	if ($result->num_rows > 0) {
		$line .= 'INSERT INTO `'.$prefix23.'product_option_value` (`product_option_value_id`, `product_option_id`, `product_id`, `option_id`, `option_value_id`, `quantity`, `subtract`, `price`, `price_prefix`, `points`, `points_prefix`, `weight`, `weight_prefix`) VALUES ';
	    // output data of each row
	    while($row = $result->fetch_assoc()) {
	    	$line .= '('.$row["product_option_value_id"].','.$row["product_option_id"].','.$row["product_id"].','.$row["product_option_id"].','.$row["product_option_value_id"].','.$row["quantity"].','.$row["subtract"].','.$row["price"].',"'.addslashes($row["prefix"]).'",0,"+",0.00,"+"),';
	    }
	    // remove last comma
	    $line = substr($line, 0, -1);
	    $line .= ';'.PHP_EOL.PHP_EOL;
	} else {
	    exit("0 results for $sql");
	}

	// Extract `product_related`
	$sql = "SELECT * FROM `".$prefix14."product_related`";
	$result = $conn->query($sql);

	if ($result->num_rows > 0) {
		$line .= 'INSERT INTO `'.$prefix23.'product_related` (`product_id`, `related_id`) VALUES ';
	    // output data of each row
	    while($row = $result->fetch_assoc()) {
	    	$line .= '('.$row["product_id"].','.$row["related_id"].'),';
	    }
	    // remove last comma
	    $line = substr($line, 0, -1);
	    $line .= ';'.PHP_EOL.PHP_EOL;
	} else {
	    exit("0 results for $sql");
	}

	// Extract `product_to_category`
	$sql = "SELECT * FROM `".$prefix14."product_to_category`";
	$result = $conn->query($sql);

	if ($result->num_rows > 0) {
		$line .= 'INSERT INTO `'.$prefix23.'product_to_category` (`product_id`, `category_id`) VALUES ';
	    // output data of each row
	    while($row = $result->fetch_assoc()) {
	    	$line .= '('.$row["product_id"].','.$row["category_id"].'),';
	    }
	    // remove last comma
	    $line = substr($line, 0, -1);
	    $line .= ';'.PHP_EOL.PHP_EOL;
	} else {
	    exit("0 results for $sql");
	}

	// Extract `product_to_store`
	$sql = "SELECT * FROM `".$prefix14."product_to_store`";
	$result = $conn->query($sql);

	if ($result->num_rows > 0) {
		$line .= 'INSERT INTO `'.$prefix23.'product_to_store` (`product_id`, `store_id`) VALUES ';
	    // output data of each row
	    while($row = $result->fetch_assoc()) {
	    	$line .= '('.$row["product_id"].','.$row["store_id"].'),';
	    }
	    // remove last comma
	    $line = substr($line, 0, -1);
	    $line .= ';';
	} else {
	    exit("0 results for $sql");
	}

	fwrite($output, $line);
	fclose($output);

	$conn->close();
?>
