CREATE TABLE users (
                       id SERIAL PRIMARY KEY,
                       firstname VARCHAR(100) NOT NULL,
                       lastname VARCHAR(100) NOT NULL,
                       email VARCHAR(150) UNIQUE NOT NULL,
                       password VARCHAR(255) NOT NULL,
                       bio TEXT,
                       enabled BOOLEAN DEFAULT TRUE
);

INSERT INTO users (firstname, lastname, email, password, bio, enabled)
VALUES (
           'Bartek',
           'Mockowy',
           'bartek.mock@mock.com',
           '$2y$10$Ww9hGlHkd0qLA.mXVOIgZOlzubXk2BoSydQNlQvjtq3vBtsXehJfy',
           'Lubi programować w JS i PL/SQL.',
           TRUE
       );
CREATE TABLE categories (
                            id SERIAL PRIMARY KEY,
                            name VARCHAR(100) UNIQUE NOT NULL
);

INSERT INTO categories (name) VALUES
                                  ('Spożywcze'),
                                  ('Czynsz'),
                                  ('Rachunki'),
                                  ('Rozrywka'),
                                  ('Transport');

CREATE TABLE groups (
                        id SERIAL PRIMARY KEY,
                        name VARCHAR(255) NOT NULL,
                        created_by_user_id INTEGER NOT NULL REFERENCES users(id),
                        invite_id UUID UNIQUE NOT NULL DEFAULT gen_random_uuid(),
                        created_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP
);
INSERT INTO groups (name, created_by_user_id) VALUES
                                                  ('Konto Wspólne', 1),
                                                  ('Wyjazd Firmowy 2024', 1),
                                                  ('Remont Mieszkania', 1);

CREATE TABLE group_members (
                               group_id INTEGER NOT NULL REFERENCES groups(id),
                               user_id INTEGER NOT NULL REFERENCES users(id),
                               joined_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
                               PRIMARY KEY (group_id, user_id)
);
INSERT INTO group_members (group_id, user_id) VALUES
                                                  (1, 1), -- Bartek w 'Konto Wspólne'
                                                  (2, 1), -- Bartek w 'Wyjazd Firmowy 2024'
                                                  (3, 1); -- Bartek w 'Remont Mieszkania'
CREATE TABLE expenses (
                          id SERIAL PRIMARY KEY,
                          group_id INTEGER NOT NULL REFERENCES groups(id),
                          paid_by_user_id INTEGER NOT NULL REFERENCES users(id),
                          amount NUMERIC(10, 2) NOT NULL CHECK (amount > 0),
                          description VARCHAR(255) NOT NULL,
                          category_id INTEGER REFERENCES categories(id),
                          photo_url VARCHAR(255),
                          date_incurred DATE DEFAULT CURRENT_DATE
);

CREATE TABLE expense_splits (
                                id SERIAL PRIMARY KEY,
                                expense_id INTEGER NOT NULL REFERENCES expenses(id) ON DELETE CASCADE,
                                user_id INTEGER NOT NULL REFERENCES users(id), -- Kto jest winien
                                amount_owed NUMERIC(10, 2) NOT NULL DEFAULT 0.00,
                                split_type VARCHAR(50) NOT NULL DEFAULT 'equal' -- np. 'equal', 'percent', 'exact'
);


CREATE TABLE settlements (
                             id SERIAL PRIMARY KEY,
                             group_id INTEGER NOT NULL REFERENCES groups(id),
                             payer_user_id INTEGER NOT NULL REFERENCES users(id), -- Kto płaci (dłużnik)
                             payee_user_id INTEGER NOT NULL REFERENCES users(id), -- Kto otrzymuje (wierzyciel)
                             amount NUMERIC(10, 2) NOT NULL CHECK (amount > 0),
                             date_settled TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP
);


CREATE TABLE shopping_lists (
                                id SERIAL PRIMARY KEY,
                                group_id INTEGER NOT NULL REFERENCES groups(id),
                                name VARCHAR(255) NOT NULL,
                                created_by_user_id INTEGER NOT NULL REFERENCES users(id),
                                created_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
                                updated_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP

);


CREATE TABLE list_items (
                            id SERIAL PRIMARY KEY,
                            list_id INTEGER NOT NULL REFERENCES shopping_lists(id) ON DELETE CASCADE,
                            name VARCHAR(255) NOT NULL,
                            subtitle VARCHAR(255),
                            quantity NUMERIC(10, 2) DEFAULT 1.0,
                            unit VARCHAR(50) DEFAULT 'szt.',
                            is_in_cart BOOLEAN DEFAULT FALSE,
                            is_purchased BOOLEAN DEFAULT FALSE,
                            purchased_by_user_id INTEGER REFERENCES users(id)
);