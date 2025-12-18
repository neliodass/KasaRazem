-- Demo data for KasaRazem application
-- This file is loaded when DEMO_MODE=true

-- Demo users
INSERT INTO users (firstname, lastname, email, password, enabled, theme)
VALUES
    -- Demo user (password: demo123)
    ('Demo', 'User', 'demo@kasarazem.pl', '$2a$12$Hv3BEHU4m3ClfacnecPw0ON9LPVw1EP1rvuyV176d7rv9SSegqAqO', TRUE, 'light'),
    -- Other demo users
    ('Anna', 'Smith', 'anna.smith@example.com', '$2a$12$LQv3c1yqBWVHxkd0LHAkCOYz6TtxMQJqhN8/LewY5eov0vB7Rh.pG', TRUE, 'light'),
    ('John', 'Doe', 'john.doe@example.com', '$2a$12$LQv3c1yqBWVHxkd0LHAkCOYz6TtxMQJqhN8/LewY5eov0vB7Rh.pG', TRUE, 'dark'),
    ('Maria', 'Garcia', 'maria.garcia@example.com', '$2a$12$LQv3c1yqBWVHxkd0LHAkCOYz6TtxMQJqhN8/LewY5eov0vB7Rh.pG', TRUE, 'light'),
    ('Peter', 'Johnson', 'peter.johnson@example.com', '$2a$12$LQv3c1yqBWVHxkd0LHAkCOYz6TtxMQJqhN8/LewY5eov0vB7Rh.pG', TRUE, 'light');

-- Demo groups
INSERT INTO groups (name, created_by_user_id)
VALUES
    ('Household', 1), -- Demo user is ID 1
    ('Mountain Trip 2024', 1),
    ('Shared Shopping', 2);

-- Group members
INSERT INTO group_members (group_id, user_id)
VALUES
    -- Household group (ID 1)
    (1, 1), -- Demo User
    (1, 2), -- Anna Smith
    (1, 3), -- John Doe
    -- Mountain Trip
    (2, 1), -- Demo User
    (2, 2), -- Anna Smith
    (2, 4), -- Maria Garcia
    (2, 5), -- Peter Johnson
    -- Shared Shopping
    (3, 2), -- Anna Smith
    (3, 3), -- John Doe
    (3, 4); -- Maria Garcia

-- Demo expenses for "Household" group
INSERT INTO expenses (group_id, paid_by_user_id, amount, description, category_id, date_incurred)
VALUES
    (1, 1, 450.00, 'Grocery shopping - Walmart', 1, CURRENT_DATE - INTERVAL '2 days'),
    (1, 2, 1200.00, 'January rent', 2, CURRENT_DATE - INTERVAL '15 days'),
    (1, 3, 320.50, 'Utilities - electricity, gas, water', 3, CURRENT_DATE - INTERVAL '10 days'),
    (1, 1, 180.00, 'Netflix, Spotify subscriptions', 4, CURRENT_DATE - INTERVAL '5 days'),
    (1, 2, 95.00, 'Uber to downtown', 5, CURRENT_DATE - INTERVAL '3 days'),
    (1, 3, 520.00, 'Grocery shopping - Costco', 1, CURRENT_DATE - INTERVAL '7 days'),
    (1, 1, 75.50, 'Movie tickets', 4, CURRENT_DATE - INTERVAL '1 day');

-- Demo expense splits for "Household" (equal split among 3 members)
INSERT INTO expense_splits (expense_id, user_id, amount_owed, split_type)
VALUES
    -- Expense 1: Grocery shopping (450.00 / 3 = 150.00 each)
    (1, 1, 150.00, 'equal'),
    (1, 2, 150.00, 'equal'),
    (1, 3, 150.00, 'equal'),
    -- Expense 2: Rent (1200.00 / 3 = 400.00 each)
    (2, 1, 400.00, 'equal'),
    (2, 2, 400.00, 'equal'),
    (2, 3, 400.00, 'equal'),
    -- Expense 3: Utilities (320.50 / 3 ≈ 106.83 each)
    (3, 1, 106.83, 'equal'),
    (3, 2, 106.83, 'equal'),
    (3, 3, 106.84, 'equal'),
    -- Expense 4: Subscriptions (180.00 / 3 = 60.00 each)
    (4, 1, 60.00, 'equal'),
    (4, 2, 60.00, 'equal'),
    (4, 3, 60.00, 'equal'),
    -- Expense 5: Uber (95.00 / 3 ≈ 31.67 each)
    (5, 1, 31.67, 'equal'),
    (5, 2, 31.67, 'equal'),
    (5, 3, 31.66, 'equal'),
    -- Expense 6: Grocery shopping 2 (520.00 / 3 ≈ 173.33 each)
    (6, 1, 173.33, 'equal'),
    (6, 2, 173.33, 'equal'),
    (6, 3, 173.34, 'equal'),
    -- Expense 7: Movies (75.50 / 3 ≈ 25.17 each)
    (7, 1, 25.17, 'equal'),
    (7, 2, 25.17, 'equal'),
    (7, 3, 25.16, 'equal');

-- Demo expenses for "Mountain Trip" group
INSERT INTO expenses (group_id, paid_by_user_id, amount, description, category_id, date_incurred)
VALUES
    (2, 1, 1800.00, 'Cabin accommodation', 4, CURRENT_DATE - INTERVAL '20 days'),
    (2, 2, 450.00, 'BBQ groceries', 1, CURRENT_DATE - INTERVAL '19 days'),
    (2, 4, 320.00, 'Gas for the car', 5, CURRENT_DATE - INTERVAL '20 days'),
    (2, 5, 180.00, 'Water park tickets', 4, CURRENT_DATE - INTERVAL '18 days');

-- Demo expense splits for "Mountain Trip" (4 members)
INSERT INTO expense_splits (expense_id, user_id, amount_owed, split_type)
VALUES
    -- Cabin (1800 / 4 = 450 each)
    (8, 1, 450.00, 'equal'),
    (8, 2, 450.00, 'equal'),
    (8, 4, 450.00, 'equal'),
    (8, 5, 450.00, 'equal'),
    -- BBQ groceries (450 / 4 = 112.50 each)
    (9, 1, 112.50, 'equal'),
    (9, 2, 112.50, 'equal'),
    (9, 4, 112.50, 'equal'),
    (9, 5, 112.50, 'equal'),
    -- Gas (320 / 4 = 80 each)
    (10, 1, 80.00, 'equal'),
    (10, 2, 80.00, 'equal'),
    (10, 4, 80.00, 'equal'),
    (10, 5, 80.00, 'equal'),
    -- Water park (180 / 4 = 45 each)
    (11, 1, 45.00, 'equal'),
    (11, 2, 45.00, 'equal'),
    (11, 4, 45.00, 'equal'),
    (11, 5, 45.00, 'equal');

-- Demo shopping lists
INSERT INTO shopping_lists (group_id, name, created_by_user_id, created_at)
VALUES
    (1, 'Weekly groceries', 1, CURRENT_DATE - INTERVAL '2 days'),
    (1, 'Holiday shopping', 2, CURRENT_DATE - INTERVAL '5 days'),
    (2, 'Trip packing list', 1, CURRENT_DATE - INTERVAL '21 days');

-- Demo list items
INSERT INTO list_items (list_id, name, subtitle, quantity, unit, is_in_cart, is_purchased, purchased_by_user_id)
VALUES
    -- Weekly groceries (active)
    (1, 'Milk', '3.2% fat', 2, 'pcs', FALSE, FALSE, NULL),
    (1, 'Bread', 'whole grain', 1, 'pcs', FALSE, FALSE, NULL),
    (1, 'Butter', 'unsalted', 1, 'pcs', FALSE, FALSE, NULL),
    (1, 'Eggs', '', 10, 'pcs', FALSE, FALSE, NULL),
    -- Already purchased
    (1, 'Cheese', 'cheddar', 0.5, 'kg', TRUE, TRUE, 1),
    (1, 'Tomatoes', '', 1, 'kg', TRUE, TRUE, 1),

    -- Holiday shopping
    (2, 'Turkey', '', 2, 'kg', FALSE, FALSE, NULL),
    (2, 'Cranberry sauce', '', 2, 'jars', FALSE, FALSE, NULL),
    (2, 'Gingerbread', '', 1, 'box', FALSE, FALSE, NULL),
    (2, 'Christmas tree', '6ft', 1, 'pcs', TRUE, TRUE, 2),

    -- Trip packing
    (3, 'Backpack', 'hiking', 1, 'pcs', TRUE, TRUE, 1),
    (3, 'Sleeping bag', '', 2, 'pcs', TRUE, TRUE, 1),
    (3, 'Flashlight', '', 1, 'pcs', FALSE, FALSE, NULL);

-- Demo settlements (some debts already settled)
INSERT INTO settlements (group_id, payer_user_id, payee_user_id, amount, date_settled)
VALUES
    (1, 2, 1, 150.00, CURRENT_DATE - INTERVAL '8 days'),
    (2, 4, 1, 300.00, CURRENT_DATE - INTERVAL '15 days');
