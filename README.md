<div align="center">
  <h1>💰 KasaRazem</h1>
  <p><strong>Smart expense management for groups</strong></p>
  
  <p>
    <a href="#features">Features</a> •
    <a href="#screenshots">Screenshots</a> •
    <a href="#tech-stack">Tech Stack</a> •
    <a href="#getting-started">Getting Started</a> •
    <a href="#demo">Demo</a>
  </p>

  ![License](https://img.shields.io/badge/license-MIT-blue.svg)
  ![PHP Version](https://img.shields.io/badge/php-%3E%3D8.1-8892BF.svg)
  ![PostgreSQL](https://img.shields.io/badge/postgresql-14+-316192.svg)
  ![Docker](https://img.shields.io/badge/docker-ready-2496ED.svg)
</div>

---

## 📖 About The Project

KasaRazem (Polish: "Cash Together") is a modern web application designed to simplify expense tracking and settlements between groups of people. Whether you're sharing an apartment, planning a trip, or managing household expenses, KasaRazem helps you keep track of who owes what and ensures fair splits.

### ✨ Key Highlights

- **🎯 Smart Debt Settlement** - Automatically calculates optimal payment paths to minimize transactions
- **🛒 Collaborative Shopping Lists** - Share and manage shopping lists with your group
- **🌍 Multilingual** - Full support for Polish and English with easy language switching
- **🎨 Modern UI/UX** - Clean, responsive design with dark/light theme support
- **🔒 Secure** - Advanced security with login auditing and CSRF protection
- **🚀 Docker Ready** - One-command deployment with Docker Compose

---

## 🎬 Screenshots

### Expense Tracking
![Expenses](/promos/expenses.gif)

### Group Management
![Groups](/promos/group_management.gif)

### Shopping Lists

![Shopping Lists](/promos/shopping_lists.gif)

### Balance & Settlements
![Balance](/promos/balance.gif)

---

## ✨ Features

### 💳 Expense Management
- Create and track shared expenses
- Multiple split methods: equal, ratio-based, or custom amounts
- Category-based organization
- Detailed expense history with date filtering

### 👥 Group Management
- Create unlimited groups for different purposes
- Invite members via unique codes
- Role-based access (creator has admin rights)
- Member management and removal

### 💰 Smart Settlements
- Automatic debt calculation
- Optimized settlement suggestions (minimum transactions)
- Visual balance overview
- One-click debt settlement confirmation

### 🛒 Shopping Lists
- Shared shopping lists per group
- Mark items as purchased
- Real-time collaboration
- Product categorization

### 🌐 Internationalization
- Auto-detection of browser language
- Support for Polish and English
- Easy language switching
- Fully translatable interface

### 🎨 User Experience
- Dark/Light theme support
- Responsive design (mobile, tablet, desktop)
- Intuitive navigation
- Custom user avatars
- Real-time updates

### 🔐 Security Features
- Secure authentication with password hashing
- CSRF protection on all forms
- Login attempt auditing (without storing passwords)
- Session management with "Remember Me" option
- Input validation and sanitization

---

## 🛠️ Tech Stack

### Backend
- **PHP 8.1+** - Modern PHP with OOP principles
- **PostgreSQL 14+** - Robust relational database

### Frontend
- **Vanilla JavaScript** - No framework dependencies
- **CSS3** - Modern styling with CSS variables
- **Material Symbols** - Google's icon library
- **Responsive Design** - Mobile-first approach


---

## 🚀 Getting Started

### Prerequisites

- Docker Engine 20.10+
- Docker Compose 2.0+

### Installation

1. **Clone the repository**
   ```bash
   git clone https://github.com/yourusername/KasaRazem.git
   cd KasaRazem
   ```

2. **Set up environment variables**
   ```bash
   cp .env.example .env
   ```

3. **Configure your environment** (optional)
   
   Edit `.env` file to customize settings:
   ```env
   # Database Configuration
   DB_HOST=db
   DB_PORT=5432
   DB_NAME=db
   DB_USER=docker
   DB_PASSWORD=docker
   
   # Demo Mode (includes sample data)
   DEMO_MODE=true
   
   # pgAdmin
   PGADMIN_DEFAULT_EMAIL=admin@example.com
   PGADMIN_DEFAULT_PASSWORD=admin
   PGADMIN_PORT=5050
   ```

4. **Launch the application**
   ```bash
   docker-compose up -d
   ```


### Demo Mode

When `DEMO_MODE=true`, the application includes:
- Sample groups and users
- Pre-populated expenses
- Example shopping lists
- Demo account credentials:
  - Email: `demo@kasarazem.pl`
  - Password: `demo123`

---

## 📱 Usage

### Creating Your First Group

1. Register a new account or log in
2. Click "Create New Group"
3. Enter a group name (e.g., "Apartment Expenses")
4. Invite members using the generated invite code

### Adding Expenses

1. Select a group from your dashboard
2. Navigate to the "Expenses" tab
3. Click "Add Expense"
4. Fill in details:
   - Name and amount
   - Category and date
   - Who paid
   - How to split (equal, ratio, or custom amounts)

### Managing Shopping Lists

1. Go to the "Shopping Lists" tab in your group
2. Create a new list or select an existing one
3. Add items you need to buy
4. Check off items as you purchase them
5. All group members see updates in real-time

### Settling Debts

1. Navigate to the "Balance" tab
2. View your current balance and who owes whom
3. Click "Settle Group" to see optimal payment suggestions
4. Confirm settlements once payments are made

---
## 🎥 Live Demo
You can try a live demo of KasaRazem at: [Currently unavailable]

---
<div align="center">
  <p>Made with ❤️ for better expense management</p>
  <p>⭐ Star this repo if you find it useful!</p>
</div>
