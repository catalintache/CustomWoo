# CustomWOO

## Detailed Description

**CustomWOO** is an advanced plugin designed for WooCommerce-based online stores, offering a wide array of customizations and additional functionalities aimed at enhancing both the shopping and administration experience. The plugin integrates solutions for dynamic discounts, customized shipping options, restrictions for the Shop Manager role, automatic WhatsApp notifications, and quantity input customizations. It leverages external services such as the Google Distance Matrix API and the Twilio API to provide sophisticated shipping calculations and automated messaging.

### Key Features

- **Dynamic Discounts and Discount Table:**  
  - Calculates discounts based on the quantity ordered.
  - Displays a discount table on the product page.
  - Dynamically updates the displayed price and cart price using WordPress filters and hooks.

- **Customized Shipping Methods:**  
  - Automatically determines the appropriate shipping method based on customer input (such as address and county) and calculated values (distance to the delivery address, total weight, and number of pallets).
  - Computes shipping costs using data from the Google Distance Matrix API to obtain the distance between the origin (warehouse) and the destination.
  - Offers multiple shipping options, including self-transport, Sameday courier, or Pallex transport, along with an additional “Pickup from Warehouse” option.

- **Integration with Google Distance Matrix API:**  
  - Utilizes a custom function (`calculezDist()`) to send requests to the Google Distance Matrix API.
  - Retrieves the distance in kilometers between the warehouse address (defined within the plugin) and the customer's address, which is essential for accurately calculating shipping costs.
  - Stores the calculated distance in the session for later use in selecting the shipping method and applying the correct rates.

- **Restrictions and Customizations for Shop Manager:**  
  - Implements redirects and access restrictions within the WordPress admin dashboard for users with the `shop_manager` role.
  - Includes features such as redirecting to the orders page, hiding non-essential admin menus, and customizing the admin bar to focus on order management.

- **Custom Order Statuses:**  
  - Defines additional order statuses such as "Paid by Card", "Processed", "OP Payment Pending", and "OP Payment Completed".
  - Automatically updates order statuses based on the selected payment method and other specific conditions relevant to the order.

- **Integrated WhatsApp Notifications (Twilio API):**  
  - Integrates automatic WhatsApp notifications using the Twilio API to provide timely updates to customers.
  - Sends automatic messages for key events (e.g., when an order is processed, when it is completed, and later a review request message with the product link).
  - Configurable credentials (SID, token, sender number, etc.) are specified in the `includes/whatsapp-notifications.php` file.

- **Quantity Input Customizations:**  
  - Customizes the appearance and functionality of the quantity input fields on product pages and in the cart.
  - Provides specific step adjustments for particular product categories (for example, pellets or briquettes).
  - Achieved through dedicated JavaScript files located in the `assets/js/` directory.

- **Centralized Global Data:**  
  - Product data such as unit weight, classification, pallet weight, etc., are defined in a global file (`includes/globals.php`).
  - Shipping rates by county and a mapping of counties to their capitals are also stored in the same file.
  - This centralized system enables complex shipping calculations and the determination of rates based on regional factors.
