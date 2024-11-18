<?php
/*
Plugin Name: Tamino Custom Shop
Description: Adds a custom "Add to Cart" button for a specific product via AJAX using a URL.
Version: 1.1
Author: Tamino Team
*/
// Enqueue the custom CSS
function tamino_enqueue_custom_styles() {
    // Link to your custom CSS file
    wp_enqueue_style( 'tamino-custom-styles', plugin_dir_url( __FILE__ ) . 'tamino-custom.css' );
}
add_action( 'wp_enqueue_scripts', 'tamino_enqueue_custom_styles' );


// Enqueue JavaScript for AJAX functionality
function tamino_enqueue_ajax_script() {
    wp_enqueue_script( 'tamino-add-to-cart', plugin_dir_url( __FILE__ ) . 'tamino-add-to-cart.js', array( 'jquery' ), null, true );

    // Pass the product ID to the script
    wp_localize_script( 'tamino-add-to-cart', 'tamino_ajax_object', array(
        'product_id' => 1017 // Replace with your product ID
    ));
}
add_action( 'wp_enqueue_scripts', 'tamino_enqueue_ajax_script' );

// Shortcode to display the AJAX "Add to Cart" link
function tamino_add_to_cart_button($atts) {
        // Use get_the_ID() to dynamically fetch the current post ID if none is provided
    $atts = shortcode_atts(
        array(
            'post_id' => get_the_ID(), // Default to the current post ID
        ),
        $atts
    );

        // Retrieve the product ID
    $tour_id = $atts['post_id'];

    // Debugging: Check if the product ID is correctly received
    if (!$tour_id) {
        return 'No product ID found';
    }else{
        //error_log("ID is: ".$tour_id);
    }


    $tour_duration_number = Get_tourDuration_Value_by_ID($tour_id);
    $tour_duration_label = Get_tourDuration_Unit_by_ID($tour_id);

    $organizer_category = Get_Organizer_by_ID($tour_id);

    if($organizer_category){
        //echo "Oragnizer is: ". $organizer_category;
    }

    if($tour_duration_number && $tour_duration_label){
        //echo "Tour duration is: ". $tour_duration_number . " ". $tour_duration_label;
        echo "<br>";
        if(strtolower($organizer_category) == "flexi pass products" ){
                switch($tour_duration_label){

                    case "day":
                    if($tour_duration_number == 1){
                        echo "Product would be for 1 day Flexi Pass";
                    }else if($tour_duration_number == 2){
                        echo "Product would be for 2 days Flexi Pass";
                    }else if($tour_duration_number == 3){
                        echo "Product would be for 3 days Flexi Pass";
                    }else{
                        echo "Product would be for 6 days Flexi Pass";
                    }
                    break;

                    case "hour":
                        echo "Product would be for 1 day Flexi Pass - because hour";
                    break;

                    case "min":
                        echo "Product would be for 1 day Flexi Pass - because minute";
                    break;          

                }

        }else if(strtolower($organizer_category) == "wien pass products"){
                switch($tour_duration_label){

                    case "day":
                    if($tour_duration_number == 1){
                        echo "Product would be for 1 Sights Wien Pass";
                    }else if($tour_duration_number == 2){
                        echo "Product would be for 2 Sights Wien Pass";
                    }else if($tour_duration_number == 3){
                        echo "Product would be for 3 Sights Wien Pass";
                    }else{
                        echo "Product would be for 6 Sights Wien Pass";
                    }
                    break;

                    case "hour":
                        echo "Product would be for 1 Sights Wien Pass - because hour";
                    break;

                    case "min":
                        echo "Product would be for 1 Sights Wien Pass - because minute";
                    break;          

                }
        }else{
            echo "No organizer found";
        }
    }else{
        echo "Tour duration not found";
    }

    $product_id = Get_productID_by_SKU($tour_id); // Replace with your product ID

    if($product_id){
        $product = wc_get_product( $product_id );
    }else{
        error_log("Get_productID_by_SKU return empty");
    }

    if ( $product ) {
        // Generate WooCommerce's add-to-cart URL
        $url = $product->add_to_cart_url();

        // HTML form structure with input fields for adults and children (without AJAX modifications)
        $form_html = '
            <form method="post" class="product-query-form">
                <div class="selection-container">
                    <!-- Adults Section -->
                    <div class="number-input-section">
                        <label for="adults">Erwachsene (Adults)</label>
                        <div class="number-input">
                            <button type="button" id="decrease" onclick="decreaseValue(\'adults\')">-</button>
                            <input type="text" id="adults" name="adults" value="1" readonly>
                            <button type="button" id="increase" onclick="increaseValue(\'adults\')">+</button>
                        </div>
                    </div>

                    <!-- Children Section -->
                    <div class="number-input-section">
                        <label for="children">Kinder (Children)</label>
                        <div class="number-input">
                            <button type="button" id="decreaseChild" onclick="decreaseValue(\'children\')">-</button>
                            <input type="text" id="children" name="children" value="0" readonly>
                            <button type="button" id="increaseChild" onclick="increaseValue(\'children\')">+</button>
                        </div>
                    </div>
                </div>

                <!-- The Add to Cart Link -->
                <a href="' . esc_url( $url ) . '" id="tamino-add-to-cart-btn" class="button add_to_cart_button">Add to Cart</a>
            </form>
        ';

        return $form_html;
    }

    return ''; // Return an empty string if the product is not found
}

add_shortcode( 'tamino_add_to_cart', 'tamino_add_to_cart_button' );

function Get_productID_by_SKU($tourid) {
    // Construct the SKU based on the current tour's post ID (e.g., Arabic tour)
    $sku = 'taminotour-' . intval($tourid);

    // Use WooCommerce's built-in function to get the product ID by the SKU
    $product_id = wc_get_product_id_by_sku($sku);

    // Return true if the product exists, otherwise false
    return $product_id;
}

function Get_tourDuration_Value_by_ID($tourid){
 
    $tour_duration_value = get_post_meta($tourid, 'ttbm_travel_duration', true);
 
    if($tour_duration_value){
        return $tour_duration_value;
    }else{
        return '';
    }
}

function Get_tourDuration_Unit_by_ID($tourid){
 
    $tour_duration_unit = get_post_meta($tourid, 'ttbm_travel_duration_type', true);
    
    if($tour_duration_unit){
        return $tour_duration_unit;
    }else{
        return '';
    }
}

function Get_Organizer_by_ID($tourid) {
    // Get the terms associated with this post in the 'ttbm_tour_org' taxonomy
    $terms = wp_get_post_terms($tourid, 'ttbm_tour_org');

    // Check if terms exist
    if (!empty($terms) && !is_wp_error($terms)) {
        // Create an array of term names
        $term_names = [];
        foreach ($terms as $term) {
            $term_names[] = $term->name;  // Add each term's name to the array
        }
        
        // Return the term names as a comma-separated string
        return implode(', ', $term_names);
    } else {
        return 'No terms found'; // If no terms, return a default message
    }
}


// Hook to render a custom field button for creating a WooCommerce product.
    add_action('acf/render_field/name=create_product_button', 'render_create_product_button');
// Hook to handle the admin post action for creating a WooCommerce product from a tour.
    add_action('admin_post_create_product_from_tour', 'handle_create_product_from_tour');

    /**
 * Enqueue the parent and child theme styles.
 *
 * Loads the parent theme's stylesheet first, followed by the child theme's stylesheet,
 * allowing the child styles to override the parent styles.
 */
function nirvana_child_style() {
    wp_enqueue_style( 'parent-style', get_template_directory_uri() . '/style.css' );
    wp_enqueue_style( 'child-style', get_stylesheet_directory_uri() . '/style.css', array('parent-style') );
}
/**
 * Add a new menu item to the primary navigation.
 *
 * This function checks if the theme location is 'primary' and prepends a new menu item to the existing navigation items.
 */
function add_new_menu_item( $nav, $args ) {
    if( $args->theme_location == 'primary' )
    //$newmenuitem = "<li class='mega-menu-item mega-menu-item-type-custom mega-menu-item-object-custom mega-current-menu-item mega-current_page_item mega-menu-item-home mega-align-bottom-left mega-menu-flyout mega-has-icon mega-icon-left'><a href='https://tamino.travel/' target='_self'><img src='https://tamino.travel/wp-content/uploads/2024/05/tamino_travel_logo.jpg'/></a></li>";
    $nav = $newmenuitem.$nav;
    return $nav;
}
/**
 * Add a new menu item to the top navigation.
 *
 * This function checks if the theme location is 'top' and prepends a new menu item to the existing top navigation items.
 */
function add_new_menu_item_top( $nav, $args ) {
    if( $args->theme_location == 'top' )
    //$newmenuitem = "<li id='custom_logo_container'class='mega-menu-item mega-menu-item-type-custom mega-menu-item-object-custom mega-current-menu-item mega-current_page_item mega-menu-item-home mega-align-bottom-left mega-menu-flyout mega-has-icon mega-icon-left'><a href='https://tamino.travel/' target='_self'><img class='custom_mobile_logo' src='https://tamino.travel/wp-content/uploads/2024/06/tamino_travel_top_navigation_small.png'/></a></li>";
    $nav = $newmenuitem.$nav;
    return $nav;
}

/**
 * Output custom CSS for the WordPress login page.
 *
 * This function modifies the appearance of the login page by adding custom styles 
 * for the body background, login form, and input fields.
 */
function the_dramatist_custom_login_css() {
    echo '<style type="text/css"> 
	body.login {
	background-image:url("https://tamino.travel/wp-content/uploads/2024/04/MEDRES_00000128055_Grossglockner-Hochalpenstrasse_Grossglockner-Hochalpenstrassen-AG_Michael-Rudolf.jpg");
	}
	
		body.login div#login h1 a {
    background-image: url("https://tamino.travel/wp-content/uploads/2024/06/login-tamino_fav_ico-remove.png");
}
body.login div#login form#loginform {
background-color:#fff;
border:10px double red;
}


body.login div#login form#loginform input {
padding: 5px;
     font-size: 16px;
     border-width: 3px;
     border-color: #040303;
     background-color: #FFFFFF;
     color: #000000;
     border-style: double;
     border-radius: 0px;
     box-shadow: 0px 0px 9px rgba(66,66,66,.75);
     text-shadow: 0px 0px 5px rgba(66,66,66,.75);
}
body.login div#login #backtoblog a{font-size:14px;color:#000 !important; font-weight:bold;}
.login #login p#nav a {font-size:14px;color:#000 !important; font-weight:bold;}


</style>';
}

/**
 * Render the "Create WooCommerce" button for a tour.
 *
 * This function checks if the current post is a published tour and displays a button 
 * that allows the creation of a WooCommerce product linked to the tour.
 *
 * @param array $field The ACF field array containing field settings.
 */
function render_create_product_button($field) {
    global $post;

    // Check if we are editing a published tour.
    if (isset($post->ID) && $post->post_type === 'ttbm_tour' && $post->post_status !== 'auto-draft') {
        $nonce = wp_create_nonce('create_product_from_tour_nonce'); // Create a nonce
        $post_id = $post->ID;

        if(!find_product_by_sku($post_id)){
        // Output the button with the nonce in the URL.
        echo '<a href="' . admin_url('admin-post.php?action=create_product_from_tour&post_id=' . $post_id . '&nonce=' . $nonce) . '" class="button button-primary">Create WooCommerce </a>';
        }
        else{
            echo '<p class="acf-info-message" style="color:green;">This tour alread has product linked</p>';
        }
    } else {
        echo '<p class="acf-info-message" style="color:red;">Save the tour first to create a WooCommerce product.</p>';
    }
}

/**
 * Handle the creation of a WooCommerce product from a tour.
 *
 * This function verifies the nonce for security, retrieves the post ID,
 * calls the product creation function, and then redirects the user back
 * to the edit screen of the tour.
 */
function handle_create_product_from_tour() {
    // Verify the nonce for security.
    if (!isset($_GET['nonce']) || !wp_verify_nonce($_GET['nonce'], 'create_product_from_tour_nonce')) {
        wp_die('Invalid request.'); // Exit if nonce verification fails
    }

    $post_id = intval($_GET['post_id']);

    // Call your existing function to create the WooCommerce product using the post ID.
    create_product_from_tour($post_id);

    //log_post_creation_status($post_id, 'Button clicked to create product.');

    // Redirect back to the edit screen with a success message.
    wp_redirect(admin_url('post.php?post=' . $post_id . '&action=edit&message=1'));
    exit;
}

/**
 * Log the status of post creation.
 *
 * This helper function writes the post ID and its status message to the error log.
 *
 * @param int $post_id The ID of the post.
 * @param string $status The status message to log.
 */
function log_post_creation_status($post_id, $status) {
    error_log('Post ID: ' . $post_id . ' - ' . $status);
}

/**
 * Check if a WooCommerce product exists by SKU.
 *
 * This function constructs an SKU based on the post ID and queries the WooCommerce products
 * to determine if a product with that SKU already exists.
 *
 * @param int $post_id The ID of the post associated with the SKU.
 * @return bool True if the product exists, false otherwise.
 */
function find_product_by_sku($tourid) {
    // Construct the SKU based on the current tour's post ID (e.g., Arabic tour)
    $sku = 'taminotour-' . intval($tourid);

    // Use WooCommerce's built-in function to get the product ID by the SKU
    $product_id = wc_get_product_id_by_sku($sku);

    // Return true if the product exists, otherwise false
    return $product_id ? true : false;
}


/**
 * Retrieves the starting price for a specific tour.
 *
 * This function uses the WordPress `get_post_meta` function to fetch the
 * 'ttbm_travel_start_price' meta value associated with the given tour ID.
 * 
 * It checks if a price exists for the specified tour:
 * - If a price is found, it logs the price for debugging purposes and returns it.
 * - If no price is found, it logs that no price was found and returns a default value of 98.
 *
 * @param int $tourid The ID of the tour for which the price is to be retrieved.
 * @return mixed The starting price of the tour if found, otherwise a default value of 98.
 */
function get_tourprice($tourid){
    $tourprice = get_post_meta($tourid, 'ttbm_travel_start_price', true);

    if($tourprice){
        return $tourprice;
    }
    else{
        return 98;
    }
}

/**
 * Create a WooCommerce product from a tour post.
 *
 * This function retrieves the tour's details, including title, description, image, and custom fields,
 * and uses them to create a new WooCommerce product. It also generates a PDF for the product and
 * sets it as a downloadable item. The product is linked to the original tour post.
 *
 * @param int $ttbm_post_id The ID of the tour post to create a product from.
 */
function create_product_from_tour($ttbm_post_id) {
    // Check if WooCommerce is active
    if (!class_exists('WC_Product_Simple')) {
        return;
    }

    $tour_language_code = pll_get_post_language($ttbm_post_id);

    //error_log('id is: '.$ttbm_post_id.'the language is: '.$tour_language_code);

    // Retrieve the post object
    $post = get_post($ttbm_post_id);

    // Get the tour's title and content (or any other meta fields)
    $tour_title = get_the_title($ttbm_post_id);

    //log_post_creation_status($ttbm_post_id, 'Tour Title hier: '.$tour_title);
    $tour_description = $post->post_content;

    //log_post_creation_status($ttbm_post_id, 'Tour Description hier: '.$tour_description);
    $image_url = wp_get_attachment_url(getimageIDfromTour($ttbm_post_id));  // Returns the URL of the image

    //log_post_creation_status($ttbm_post_id, 'Tour image url hier: '.$image_url);
    $image_path = get_attached_file(getimageIDfromTour($ttbm_post_id));  // Returns the file path of the image

    //log_post_creation_status($ttbm_post_id, 'Tour image path hier: '.$image_path);
    $day_details = get_post_meta($ttbm_post_id, 'ttbm_daywise_details', array());

    // Prefixing with "tour-"
    $sku = 'taminotour-' . $ttbm_post_id; 

    // The pdf name showen in the dashboard of Woocommerce
    $pdfname = $sku;

    // Create a new WooCommerce product
    $product = new WC_Product_Simple();

    // Use tour's title
    $product->set_name($tour_title); 

    // Use tour's content as description
    $product->set_description($tour_description); 

    // Use custom field or default price
    $product->set_regular_price(get_tourprice($ttbm_post_id)); 
    
    // Publish the product
    $product->set_status('publish'); 

    // Make it visible in the catalog
    $product->set_catalog_visibility('visible'); 
    
    // Set the SKU using the tour ID with a prefix
    $product->set_sku($sku);
    
    //call function to set image id
    $product->set_image_id(getimageIDfromTour($ttbm_post_id));
    
    //changing the product to a downloadable product
    $product->set_downloadable(true);
    
    //set the product to a virtual product
    $product->set_virtual(true);
    
    // Call a function to generate the PDF and return the
    $pdf_url = create_pdf($ttbm_post_id,$tour_title,$tour_description,$image_path,$day_details);  
    
    // Generate an MD5 hash of the PDF URL to create a unique identifier for the download file.
    $file_md5 = md5($pdf_url);

    // Create a new instance of the WooCommerce product download class.
    // This represents a downloadable product in WooCommerce.
    $download = new WC_Product_Download();

    // Set the name of the PDF file as it will appear in the WooCommerce dashboard.
    $download->set_name($pdfname);

    // Set the unique ID for the download using the MD5 hash generated from the PDF URL.
    $download->set_id($file_md5);

    // Assign the actual file URL of the generated PDF to the download object.
    $download->set_file($pdf_url);

    // Store the download object in an associative array using the MD5 hash as the key.
    // This array will hold all the downloads associated with the product.
    $downloads[$file_md5] = $download;

    // Set the downloads for the WooCommerce product using the array of download objects.
    $product->set_downloads($downloads);

    // Get the category term for "Tourideen"
    switch($tour_language_code){
        
        case"de":
        $tourideen_category = get_term_by('name', 'Tourideen', 'product_cat');
        if ($tourideen_category) {
            // Assign the "Tourideen" category directly
            $product->set_category_ids(array($tourideen_category->term_id));
        }
        break;

        case"ar":
        $tourideen_category = get_term_by('name', 'فئة الاختبار', 'product_cat');
        if ($tourideen_category) {
            // Assign the "Tourideen" category directly
            $product->set_category_ids(array($tourideen_category->term_id));
        }
        break;

        default:
        $tourideen_category = get_term_by('name', 'Uncategorized', 'product_cat');
        if ($tourideen_category) {
            // Assign the "Tourideen" category directly
            $product->set_category_ids(array($tourideen_category->term_id));
        }
        break;
    }
        
        // Add the logic to limit the product to 1 per order
    // Set the product to be sold individually (limit to 1 per order)
    $product->update_meta_data('_sold_individually', 'yes');

    if ($tour_language_code === 'ar') {
            // Retrieve the German sibling ID of the tour
            $german_sibling_id = pll_get_post($ttbm_post_id, 'de');

            if ($german_sibling_id) {
                // Find the existing German product by SKU
                $german_product_id = find_product_by_sku_again($german_sibling_id);
            }
        }



    // Save the product to the database and return the product ID.
    // This also links the downloadable PDF to the product.
    $product_id = $product->save();

    // Set the product's language to match the tour's language
    pll_set_post_language($product_id, $tour_language_code);

    // Debug log for confirmation
    //error_log('Product created with ID: ' . $product_id . ' from Tour ID: ' . $ttbm_post_id);

    // If a German product ID was found, link it to the new Arabic product
    if ($german_product_id) {
        pll_save_post_translations([
            'de' => $german_product_id,
            'ar' => $product_id,
        ]);
    }

    error_log("Product created and linked. German ID: $german_product_id, Arabic ID: $product_id");
}

/**
 * Retrieve the image ID associated with a tour.
 *
 * This function fetches the featured image ID of a tour post. 
 * It can be modified to retrieve an image from a custom field if needed.
 *
 * @param int $tour_id The ID of the tour post to get the image ID from.
 * @return int The ID of the associated image.
 */
function getimageIDfromTour($tour_id) {

    // Retrieves the featured image ID
    $image_id = get_post_thumbnail_id($tour_id); 
    
    // Return the retrieved image ID
    return $image_id; 
}

/**
 * Encode text for FPDF compatibility.
 *
 * This function converts text from UTF-8 to ISO-8859-1 encoding 
 * for proper rendering in FPDF. If encoding fails, it logs the 
 * error and returns a fallback error message.
 *
 * @param string $text The text to be encoded.
 * @return string The encoded text or an error message.
 */
function encode_for_fpdf($text) {
    try {
        // Attempt to convert the encoding
        $encoded_text = iconv('UTF-8', 'ISO-8859-1//TRANSLIT', $text);
        if ($encoded_text === false) {
            throw new Exception('Encoding failed: Invalid character detected.');
        }
        return $encoded_text;
    } catch (Exception $e) {
        // Log the detailed error message
        error_log('Encoding Error: ' . $e->getMessage());
        return 'Error: Could not encode text.';
    }
}

//require_once(get_stylesheet_directory() . '/tcpdf_src/tcpdf.php');

require_once(plugin_dir_path( __FILE__ ) . 'tcpdf_src/tcpdf.php');


/*class CustomPDF extends TCPDF{
// Header method to place the logo
    public function Header() {
        // Path to your logo
        $logo_path = get_stylesheet_directory() . '/logo_200.png';
        
        // Check if the logo exists bef ore trying to add it
        if (file_exists($logo_path)) {
            // Add Logo at position (10, 10) with a width of 30mm
            $this->Image($logo_path, 160, 10, 35, 0, '', '', '', false, 300, '', false, false, 0, false, false, false);
        } else {
            error_log("Logo file not found at: " . $logo_path);
        }
    }

    public function Footer() {
                // Set font for the footer
            $this->SetY(-15); // Position at 15 mm from bottom

            // Web address on the left
            $this->SetFont('dejavusans', 'I', 8);
            $this->SetX(10); // Position at 10 mm from the left
            $this->Cell(0, 10, 'www.tamino.travel', 0, 0, 'L');

            // Page number on the right
            $this->SetX(-30); // Move cursor to 30 mm from the right edge
            $this->Cell(0, 10, $this->getAliasNumPage() . ' / ' . $this->getAliasNbPages(), 0, 0, 'R');

    }
}*/

class CustomPDF extends TCPDF {
    // Empty Header method to avoid default behavior
    public function Header() {
        // No logo or content here
    }

    public function Footer() {
        // Set font for the footer
        $this->SetY(-15); // Position at 15 mm from bottom
        $this->SetFont('dejavusans', 'I', 8);

        // Total width of the page
        $pageWidth = $this->getPageWidth();

        // Website address on the left
        $this->SetX(10); // Position at 10 mm from the left
        $this->Cell(0, 10, 'www.tamino.travel', 0, 0, 'L');

        // Logo in the center
        $logo_path = get_stylesheet_directory() . '/logo_200.png';
        if (file_exists($logo_path)) {
            // Calculate the center position for the logo
            $logoWidth = 25; // Adjust the width of the logo as necessary
            $logoX = ($pageWidth - $logoWidth) / 2; // Center the logo
            $this->Image($logo_path, $logoX, $this->getY() + 0, $logoWidth, 0, '', '', '', false, 300, '', false, false, 0, false, false, false);
        } else {
            error_log("Logo file not found at: " . $logo_path);
        }

        // Page number on the right
        $this->SetX(-30); // Move cursor to 30 mm from the right edge
        $this->Cell(0, 10, 'Page ' . $this->getAliasNumPage() . ' of ' . $this->getAliasNbPages(), 0, 0, 'R');
    }
}


/**
 * Create a PDF document from a tour's details.
 *
 * This function generates a PDF file using FPDF with the tour's title, 
 * content, and day-wise details. It includes an image if available, 
 * retrieves serialized day details from the database, and saves 
 * the PDF to the WordPress uploads directory.
 *
 * @param int $ttbm_post_id The ID of the tour post.
 * @param string $product_Title The title of the product (tour).
 * @param string $plain_text_content The main content for the PDF.
 * @param string $image_path The file path of the tour image.
 * @param array $day_details An array of details for each day of the tour.
 * @return string The URL of the generated PDF file.
 */
function create_pdf($ttbm_post_id, $product_Title, $plain_text_content, $image_path, $day_details) {
    // Include the FPDF library
    //require(get_stylesheet_directory() . '/fpdf_src/fpdf.php');

        // Function to encode text for TCPDF (if necessary)


    function encode_for_tcpdf($text) {
        return mb_convert_encoding($text, 'UTF-8', 'auto'); // Ensure text is UTF-8 encoded
    }
    
    /*try {
        require_once(get_stylesheet_directory() . '/tcpdf_src/tcpdf.php');
    } catch(Exception $e) {
        error_log("Loading TCPDF error: " . $e->getMessage());
        return false;
    }*/


    // Create upload directory if it doesn't exist
    $upload_dir = wp_upload_dir();
    $pdf_dir = $upload_dir['basedir'] . '/pdfs';
    
    // Create the pdfs directory if it doesn't exist
    if (!file_exists($pdf_dir)) {
        wp_mkdir_p($pdf_dir);
    }

    // Check if directory is writable
    if (!is_writable($pdf_dir)) {
        error_log("PDF directory is not writable: " . $pdf_dir);
        return false;
    }

    // Sanitize the filename
    $sanitized_title = sanitize_file_name($product_Title);
    $pdf_filename = 'product_' . $sanitized_title . '_' . time() . '.pdf';
    $pdf_path = $pdf_dir . '/' . $pdf_filename;
    $pdf_url = $upload_dir['baseurl'] . '/pdfs/' . $pdf_filename;

    // Define properties
    $font_name = 'dejavusans';
    $lineheight = 1.5;             // Line height for cells
    $fontsize_header = 13;         // Font size for headers
    $fontsize_content = 10;        // Font size for content
    $shortdescription_textlength = 447; // Lengh of the short description
    $description_textlegth = 1932; // Lengh of the description
    $place_textlength = 300; // Lengh of the places description
    $dayli_activity_textlength = 1600; // Lengh of the dayli activities description
    $tour_language_code = pll_get_post_language($ttbm_post_id);

    $stunden_de = "Stunden";
    $stunden_ar = "ساعات";
    $stunden_default = "Hours";
    $tage_de = "Tage";
    $tage_ar = "أيام";
    $tage_default = "Days";
    $minuten_de = "Minuten";
    $minuten_ar = "دقائق";
    $minute_default = "Minutes";
    $tour_duration_label = "";

    $tour_activities_de = "Aktivitäten der Tour";
    $tour_activities_ar = "أنشطة الجولة";
    $tour_activities_default = "Tour Activities";
    $tour_activities_label = "";

    $tour_places_de = "Diese Orte werden Sie sehen";
    $tour_places_ar = "هذه الأماكن سترونها";
    $tour_places_default = "These places you will see.";
    $tour_places_label = "";

    $tour_stations_de = "Stationen ihrer Tour";
    $tour_stations_ar = "محطات جولتك";   
    $tour_stations_default = "Stations of your tour";  
    $tour_stations_label = "";

    try {

        // Create a new PDF document
        $pdf = new CustomPDF();
        //$pdf = new CustomPDF();
        
        // Set document information
        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetAuthor('Tamino Travel');
        $pdf->SetTitle('Tramino Travel Tour');

        // Set margins
        $pdf->SetMargins(10, 10, 10);
        $pdf->SetAutoPageBreak(TRUE, 10);
        
        // Disable headers and footers
        $pdf->setPrintHeader(true);
        $pdf->setPrintFooter(true);
        

        /*******************************************************************
         * ************************ TOUR TITLE  ****************************
         *******************************************************************/
        // Add a page
        $pdf->AddPage();

        // Set font
        $pdf->SetFont($font_name, 'B', $fontsize_header);
        
        /**
         * Content starts here
         */
        // Add the product title
        $pdf->Cell(0, 0, encode_for_tcpdf($product_Title), 0, 1);
        // Line break after the title
        $pdf->Ln(1);  


        /******************************************************************
         ************************ TOUR DURATION ***************************
         ******************************************************************/
        // Retrieve and unserialize post meta data
        $tour_duration_serialized = get_post_meta($ttbm_post_id, 'ttbm_travel_duration', true);
        $tour_duration = maybe_unserialize($tour_duration_serialized);

        $tour_duration_unit_serialized = get_post_meta($ttbm_post_id, 'ttbm_travel_duration_type', true);
        $tour_duration_unit = maybe_unserialize($tour_duration_unit_serialized);

        // Set font once before outputting
        $pdf->SetFont($font_name, '', $fontsize_content);

        // Check that duration data exists
        if (!empty($tour_duration) && !empty($tour_duration_unit)) {
            switch ($tour_duration_unit) {
                case "hour":
                    if($tour_language_code == "de"){
                        $tour_duration_label = $stunden_de;
                     }elseif($tour_language_code == "ar"){
                        $tour_duration_label = $stunden_ar;
                     }else{
                        $tour_duration_label = $stunden_default;
                     }
                    $pdf->Cell(0, $lineheight, $tour_duration . ' ' .$tour_duration_label, 0, 1);
                    break;

                case "day":
                    if($tour_language_code == "de"){
                        $tour_duration_label = $tage_de;
                     }elseif($tour_language_code == "ar"){
                        $tour_duration_label = $tage_ar;
                     }else{
                        $tour_duration_label = $tage_default;
                     }
                    $pdf->Cell(0, $lineheight, $tour_duration . ' '.$tour_duration_label, 0, 1);
                    break;

                case "minute":
                    if($tour_language_code == "de"){
                        $tour_duration_label = $minuten_de;
                     }elseif($tour_language_code == "ar"){
                        $tour_duration_label = $minuten_ar;
                     }else{
                        $tour_duration_label = $minute_default;
                     }
                    $pdf->Cell(0, $lineheight, $tour_duration . ' ' . $tour_duration_label, 0, 1);
                    break;

                default:
                    $pdf->Cell(0, $lineheight, $tour_duration, 0, 1);
                    break;
            }
        } else {
            // Optionally handle missing or empty data here if needed
        }

        $pdf->Ln(5); // Move cursor down after output

        /*******************************************************************
         * ************************ TOUR IMAGE  ****************************
         *******************************************************************/
        // Set font
        $pdf->SetFont($font_name, '', $fontsize_content);

         // If image exists, place it on the left and wrap the text around
        if ($image_path && file_exists($image_path)) {
            $image_width = 40;  // Width of the image
            $image_height = 40;  // Height of the image

            // Insert the image at the top left corner (adjust the position as needed)
            $pdf->Image($image_path, 10, 30, $image_width, $image_height);

            // Set the starting point for text to the right of the image
            $pdf->SetXY($image_width + 20, 30);  // Adjust X to be to the right of the image

            // Wrap text around the image using MultiCell for the first portion
            $text_width = 190 - ($image_width + 20);  // Adjust text width to fit next to the image
            $shortdescription = get_post_meta($ttbm_post_id,'ttbm_short_description',true);
            //$pdf->MultiCell($text_width, $lineheight, encode_for_tcpdf($shortdescription), 0, 'L', 0, 1, '', '', true);  // Adjust length for wrapping

            $shortdescription_html = '<p style="line-height: '.$lineheight.';">' . nl2br(encode_for_tcpdf(substr($shortdescription, 0, $shortdescription_textlength))) . '</p>';
            $pdf->writeHTMLCell($text_width, 0, '', '', $shortdescription_html, 0, 1, 0, true, 'L', true);


            // Move cursor below the image to continue normal text wrapping
            $pdf->SetXY(10, 30 + $image_height + 5);  // Adjust position below the image
        }
        /***********************************************************************
         ************************* GALLERY IMAGES *******************************
         ************************************************************************/
        
        //This feature crashes the server - running out of memory

         // Retrieve the post meta using WordPress's standard function
        /*$gallery_images_serialized = get_post_meta($ttbm_post_id, 'ttbm_gallery_images', true);
        $gallery_images = maybe_unserialize($gallery_images_serialized);

        $image_width = 40;   // Set the width of each image
        $image_height = 40;  // Set the height of each image
        $x_position = 10;    // Starting X position for the first image
        $y_position = $pdf->GetY();  // Current Y position in the PDF
        $spacing = 5;        // Spacing between images
        $counter = 0;
        // Check if gallery images are valid and proceed
        if (is_array($gallery_images)) {

            
            foreach ($gallery_images as $gallery_image_id) {
                // Get the image URL from the attachment ID
                $image_url = wp_get_attachment_url($gallery_image_id);
                
                if ($image_url && $counter < 4) {

                    // Place the image in the PDF
                    $pdf->Image($image_url, $x_position, $y_position, $image_width, $image_height);
                    
                    // Update X position for the next image
                    $x_position += $image_width + $spacing;
                    
                    // If X position exceeds page width, reset X and move to the next line
                    if ($x_position + $image_width > $pdf->GetPageWidth() - 10) { // Adjust for right margin
                        $x_position = 10; // Reset to initial X position
                        $y_position += $image_height + $spacing; // Move Y down for a new row
                    }
                    $counter++;
                
                    
                } else {
                    error_log("Image not found for ID: " . $gallery_image_id);
                }
            }
            // Insert a new Line consisting off the image height plus 10
            $pdf->Ln($image_height + 10);
        } else {
            // Write Error-message if images are not found to error log
            error_log("Gallery images meta is not an array or is invalid.");
        }*/

        /*******************************************************************
         ******************** TOUR DESCRIPTION  ****************************
         *******************************************************************/
        // Continue with the rest of the text after the image
        $plain_text_content_html = '<p style="line-height:'.$lineheight.';">'.nl2br(encode_for_tcpdf(substr($plain_text_content, 0, $description_textlegth))).'</p>';
        $pdf->writeHTMLCell(0, 0, '', '', $plain_text_content_html, 0, 1, 0, true, 'L', true);

        /********************************************************************
        ************************ ACTIVITIES LIST ****************************
        *********************************************************************/
        // Retrieve and unserialize the stored list of activity term IDs
        $activities_list_serialized = get_post_meta($ttbm_post_id, 'ttbm_tour_activities', true);
        $activities_list = maybe_unserialize($activities_list_serialized);

        if (is_array($activities_list) && !empty($activities_list)) {
            $pdf->Ln(2); // Add some spacing after the heading

            // Set the title for the activity section in the PDF
            $pdf->SetFont($font_name, 'B', $fontsize_header); // Set bold font for heading

            if($tour_language_code == "de"){
                $tour_activities_label = $tour_activities_de;
            }elseif($tour_language_code == "ar"){
                $tour_activities_label = $tour_activities_ar;
            }else{
                $tour_activities_label = $tour_activities_default;
            }
            $pdf->Cell(0, 10, $tour_activities_label, 0, 1, 'L'); // Left-aligned heading
            $pdf->Ln(2); // Add some spacing after the heading
            
            // Set regular font for activity list items
            $pdf->SetFont($font_name, '', $fontsize_content);
            
            foreach ($activities_list as $activity) {
                    $pdf->MultiCell(0, 5, "- " . $activity, 0, 'L', false); // Use bullet point style
                    $pdf->Ln(2); // Add spacing between items
                
            }
        } 
        /*else {
            // Handle case where no activities are available
            $pdf->SetFont($font_name, 'I', $fontsize_content); // Set italic font for "no activities" message
            $pdf->MultiCell(0, 5, "No activities listed for this tour.", 0, 'L', false);
            $pdf->Ln(5);
        }*/


        /***********************************************************************
         ************************** TOUR PLACES ********************************
         ***********************************************************************/
        global $wpdb;    

        // Retrieve the post meta using WordPress's standard function
        $locations = get_post_meta($ttbm_post_id, 'ttbm_hiphop_places', true);

        // Check if the meta value is serialized (it usually is handled by get_post_meta)
        if (!empty($locations) && is_serialized($locations)) {
            $locations = maybe_unserialize($locations);
        }

        // Check if $locations is an array
        if (is_array($locations) && count($locations) > 0) {
            //error_log("Locations Array length: ".count($locations));
            // Add a new page
            $pdf->AddPage();

            // Output the hardcoded line
            $pdf->SetFont($font_name, 'B', $fontsize_header); // Set font for the heading (bold, size 12)
            if($tour_language_code == "de"){
                $tour_places_label = $tour_places_de;
            }elseif($tour_language_code == "ar"){
                $tour_places_label = $tour_places_ar;
            }else{
                $tour_places_label = $tour_places_default;
            }
            $pdf->Cell(0, 10, $tour_places_label, 0, 1, 'C'); // Centered title
            $pdf->Ln(10); // Add a line break after the heading

            // Image and text layout variables
            $image_width = 40;  // Width of the image
            $image_height = 40; // Height of the image
            $text_width = 190 - ($image_width + 20);  // Width for text (PDF width - image + padding)
            $line_height = 5;   // Line height for text

            // Iterate over each day's details
            foreach ($locations as $location) {
                // Extract individual fields
                $place_id = isset($location['ttbm_city_place_id']) ? $location['ttbm_city_place_id'] : 'No ID';
                $place_label = isset($location['ttbm_place_label']) ? $location['ttbm_place_label'] : 'No Label';

                if ($place_id) {
                    // Retrieve the place post data
                    $place_post = get_post($place_id);

                    if ($place_post && $place_post->post_type === 'ttbm_places') {
                        // Extract post title and featured image URL
                        $place_title = $place_post->post_title;
                        $place_image_id = get_post_thumbnail_id($place_id);
                        $place_content = strip_tags($place_post->post_content);  // Remove HTML tags
                        $featured_image_url = wp_get_attachment_url($place_image_id);

                        // Output the featured image if available
                        if ($featured_image_url) {
                            $current_y = $pdf->GetY();  // Store current Y position to align text
                            $pdf->Image($featured_image_url, 10, $current_y, $image_width, $image_height); // Output image
                        }

                        // Set X and Y to position text next to the image
                        $pdf->SetXY(10 + $image_width + 10, $current_y);  // Adjust X with padding

                        // Output the place title
                        $pdf->SetFont($font_name, '', $fontsize_content);
                        $pdf->MultiCell($text_width, $line_height, encode_for_tcpdf($place_title));

                        // Add a small line break after the title
                        $pdf->Ln(2);  // Adjust the value if more space is needed

                        // Set X and Y to position text next to the image
                        $pdf->SetXY(10 + $image_width + 10, $pdf->GetY());  // Adjust X with padding
                        // Output the place content below the title
                        $pdf->SetFont($font_name, '', $fontsize_content); // Regular font for content
                        $plain_text_content_html = '<p style="line-height:'.$lineheight.';">'.nl2br(encode_for_tcpdf(substr($place_content, 0, $place_textlength))).'</p>';
                        $pdf->writeHTMLCell(0, 0, '', '', $plain_text_content_html, 0, 1, 0, true, 'L', true);
                        // Move the cursor below the image for the next entry
                        $pdf->SetY($current_y + $image_height + 10);  // Move Y down to avoid overlap
                    }
                }
            }
        } /*else {
            // Handle the case where the array is not properly initialized
            $pdf->Cell(0, 10, 'No day details available.', 0, 1);
        }*/


        /********************************************************************************
         **************************** TOUR DAYWISE DETAILS ******************************
         ********************************************************************************/
        // Retrieve the post meta using WordPress's standard function
        $day_details = get_post_meta($ttbm_post_id, 'ttbm_daywise_details', true);
        
        // Check if the meta value is serialized (it usually is handled by get_post_meta)
        if (!empty($day_details) && is_serialized($day_details)) {
            $day_details = maybe_unserialize($day_details);
        }

        // Check if $day_details is an array
        if (is_array($day_details)) {
            // Add a new page for the day-wise details
            $pdf->AddPage();

            // Output the hardcoded line
            $pdf->SetFont($font_name, 'B', $fontsize_header); // Set font for the heading (bold, size 12)
            if($tour_language_code == "de"){
                $tour_stations_label = $tour_stations_de;
            }elseif($tour_language_code == "ar"){
                $tour_stations_label = $tour_stations_ar;
            }else{
                $tour_stations_label = $tour_stations_default;
            }
            $pdf->Cell(0, 10, $tour_stations_label, 0, 1, 'C'); // Centered title
            $pdf->Ln(10); // Add a line break after the heading


            // Iterate over each day's details
            foreach ($day_details as $day) {
                // Extract individual fields
                $day_title = isset($day['ttbm_day_title']) ? $day['ttbm_day_title'] : 'No title';
                $day_image = isset($day['ttbm_day_image']) ? $day['ttbm_day_image'] : '';
                $day_content = isset($day['ttbm_day_content']) ? $day['ttbm_day_content'] : 'No content';

                // Decode and clean up the content
                $decoded_content = html_entity_decode($day_content);
                $clean_content = strip_tags($decoded_content);

                // Convert the image IDs string into an array
                $image_ids = explode(',', $day_image);

                // Example output into PDF (you can modify this as needed)
                $pdf->SetFont($font_name, '', $fontsize_header);  // Set bold font for title
                $pdf->Cell(0, 10, encode_for_tcpdf($day_title), 0, 1, 'L'); // Centered title
                //$pdf->MultiCell(0, 10, encode_for_tcpdf($day_title));
                $pdf->Ln(5);

                // Display images side by side
                $x = $pdf->GetX();  // Current X position
                $y = $pdf->GetY();  // Current Y position
                $image_width = 30;   // Adjust the width of each image
                $image_height = 30;  // Adjust the height of each image
                $spacing = 5;        // Space between images

                foreach ($image_ids as $image_id) {
                    // Get the local path of the image
                    $image_path = get_attached_file($image_id);

                    if ($image_path && file_exists($image_path)) {
                        // Output the image at the current X and Y position
                        $pdf->Image($image_path, $x, $y, $image_width, $image_height);
                        $x += $image_width + $spacing;  // Move the X position for the next image
                    } else {
                        //if not found - write into Error Log
                        error_log("Image not found or invalid path: $image_path");
                    }
                }

                // Reset the Y position and add some space below the images
                $pdf->Ln($image_height + 10);

                // Output the content
                $pdf->SetFont($font_name, '', $fontsize_content);  // Set regular font for content
                //$pdf->MultiCell(0, $lineheight, encode_for_tcpdf($clean_content));
                //$pdf->MultiCell(0, $line_height, encode_for_tcpdf(substr($clean_content, 0, 300) . '...'), 0, 'L', false);

                $clean_content_html = '<p style="line-height: '.$lineheight.';">'.nl2br(encode_for_tcpdf(substr($clean_content, 0, $dayli_activity_textlength))).'</p>';
                $pdf->writeHTMLCell(0, 0, '', '', $clean_content_html, 0, 1, 0, true, 'L', true);
                $pdf->Ln(10);  // Add space between days
            }
        } else {
            // Handle the case where the array is not properly initialized
            $pdf->Cell(0, 10, 'No day details available.', 0, 1);
        }
        /**
         * Content ends here
         */
        // Output the PDF to file
        $pdf->Output($pdf_path, 'F');
        // Check to see if the PDF was created
        if (file_exists($pdf_path)) {
            // Return the pdf path
            return $pdf_url;
        } else {
            error_log("PDF file was not created: " . $pdf_path);
            return false;
        }
    // In case of an Error - throw an exeption    
    } catch (Exception $e) {
        error_log("PDF generation error: " . $e->getMessage());
        return false;
    }
}

// Function to create a custom "Add to Cart" button for a specific product
function custom_add_to_cart_button($atts) {
    // Use get_the_ID() to dynamically fetch the current post ID if none is provided
    $atts = shortcode_atts(
        array(
            'post_id' => get_the_ID(), // Default to the current post ID
        ),
        $atts
    );

    // Retrieve the product ID
    $tour_id = $atts['post_id'];

    // Debugging: Check if the product ID is correctly received
    if (!$tour_id) {
        return 'No product ID found';
    }

	$product_id=find_product_by_sku_again($tour_id);

    // Attempt to load the product
    $product = wc_get_product($product_id);

    // Debugging: Check if the product is a valid WooCommerce product
    if (!$product) {
        return 'Product not found or not a valid WooCommerce product of '.$product_id;
    }

    // If the product exists, generate the Add to Cart button
    $url = '?add-to-cart=' . $product_id;
    return '<a href="' . esc_url($url) . '" class="button add_to_cart_button">Add to Cart</a>';
}

// Register the shortcode
add_shortcode('custom_add_to_cart', 'custom_add_to_cart_button');


function find_product_by_sku_again($tourid) {
    // Construct the SKU based on the current tour's post ID (e.g., Arabic tour)
    $sku = 'taminotour-' . intval($tourid);

    // Use WooCommerce's built-in function to get the product ID by the SKU
    $product_id = wc_get_product_id_by_sku($sku);

    // Return true if the product exists, otherwise false
    return $product_id;
}