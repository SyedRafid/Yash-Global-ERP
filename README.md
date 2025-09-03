# Yash Global ERP

A **web-based ERP system** designed for large distributors who purchase products directly from manufacturers and sell them to retailers.  
This system was originally built for **Yash Global SDN BHD**, but it is general enough for any distribution business.  

🚫 **Note:** This ERP does **not** include an HR module.  

---

## ✨ Features & Modules

### 🔑 User Roles
- **Super Admin**
- **Admin**
- **Salesman**

---

### 👤 Super Admin
- All features of Admin + Salesman
- Full **User Management**: Create, update, and delete admins, salesmen, and customers
- Manage roles and access control

---

### 🛠 Admin Modules
#### Profile
- Update personal info and password

#### Dashboard
- Overview: Total customers, products, orders, sales, daily sales, expenses, dues
- Recent activity: 10 latest orders, 10 latest returns

#### Manage Products
- **Suppliers**: Add, view, modify supplier details
- **Categories**: Add, view, modify categories
- **Products**: Add, view, modify products  
  - Assign supplier and category  
  - Disable products (hidden from restock/distribution functions)

#### Manage Inventory
- **Add Inventory**: Restock products
- **Adjustments**: Correct stock numbers
- **History**: Full transaction log (restock, out, sales, updates, users involved)

#### Distribution
- Allocate products to salesmen for resale to retailers

#### Reporting
- **Sales Reports**: Per salesman, per day
- **Orders**: View all customer orders via salesmen
- **Expenses**: Track expenses logged by salesmen (fuel, tolls, food, etc.)
- **Returns**: Track customer returns

---

### 🚚 Salesman Modules
#### Profile
- Update info and password

#### Dashboard
- Overview: Active customers, assigned products, personal sales stats
- Recent activity: 10 latest orders & payments

#### Manage Customers
- Add, view, update retailer info

#### Products
- View active product catalog with status

#### Inventory
- **Restock**: Return unsold items back to main inventory
- **History**: Track personal product movement

#### Distribution
- Add products from inventory to cart  
- Apply discounts, choose customer, select payment method  
- Supports partial payments → calculates dues automatically

#### Expense Log
- Record daily expenses (food, fuel, toll, etc.)

#### Receipts & Payments
- Print order receipts  
- Manage customer due payments

#### Returns
- Create returns (select order & products, return quantities, handle refunds)
- Print return receipts

#### Reporting
- **Sales Report**: Daily or custom date range
- **Orders**: View all personal orders
- **Expenses**: View personal expenses
- **Returns**: Track customer returns

---

## 🏗 Future Roadmap
- Customer (retailer) self-service accounts
- Direct online ordering by customers
- Optional 3rd-party delivery system integration

---

## 🛠 Tech Stack
- **Frontend**: HTML, CSS, Bootstrap, JavaScript, jQuery  
  *(Template inspired by [CodeAstro Restaurant POS System](https://codeastro.com/restaurant-pos-system-in-php-with-source-code/))*  
- **Backend**: PHP  
- **Database**: MySQL  

---

## 🚀 Setup Instructions

### ✅ Requirements

- PHP 7.4+
- MySQL
- Apache Server (e.g., XAMPP, WAMP, LAMP)
- phpMyAdmin (for DB import)

### 📥 1. Clone the Project

```bash
git clone https://github.com/SyedRafid/Yash-Global-ERP.git
cd Yash-Global-ERP
```

### 📂 2. Importing the Database using phpMyAdmin

This project uses a MySQL database named **`rposystem`**. To set it up locally, follow these steps:

1. **Create the Database:**

   - Open **phpMyAdmin** in your browser (e.g., http://localhost/phpmyadmin).
   - Click on the **Databases** tab.
   - In the "Create database" field, enter the name:
     ```
     rposystem
     ```
   - Choose the collation (e.g., `utf8mb4_general_ci`) and click **Create**.

2. **Import the SQL File:**

   - Click on the newly created `rposystem` database in phpMyAdmin.
   - Go to the **Import** tab.
   - Click **Choose File** and browse to the project folder's `database` directory.
   - Select the SQL file (e.g., `rposystem.sql`).
   - Click **Go** at the bottom to start the import.
   - Wait for the success message confirming the import.

### 🗝️ Super Admin Login (Default)

- **Email:** syed.shuvon@gmail.com
- **Password:** syed.shuvon@gmail.com

### 🗝️ Admin Login (Default)

- **Email:** admin1@gmail.com
- **Password:** admin1@gmail.com

### 🗝️ Salesman Login (Default)

- **Email:** Distributor1@gmail.com
- **Password:** Distributor1@gmail.com

> ⚠️ This is the default account. Please log in and change the password immediately after setup for security.

---

## 🙏 Thank You!

Thank you for checking out **Yash Global ERP**!  
If you find this project useful, please consider giving it a ⭐️ on GitHub.  

Feel free to open issues or submit pull requests — feedback and contributions are always welcome!  

Happy coding — and best of luck managing your distribution, sales, inventory, and orders efficiently! 🚚📦✨

