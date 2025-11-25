import pandas as pd
import numpy as np
import random
from datetime import datetime, timedelta
import mysql.connector
from sklearn.ensemble import RandomForestRegressor
from sklearn.model_selection import train_test_split
import mysql.connector

db = mysql.connector.connect(
    host="yamabiko.proxy.rlwy.net",
    port=26014,
    user="root",
    password="vIvmfGZndezStcBioxxwoLlYTHoQaXHe",
    database="railway"
)

cursor = db.cursor(dictionary=True)

# Get bin list
cursor.execute("SELECT id, name FROM bins")
bins = cursor.fetchall()

# Get latest waste levels
bin_data = []
for bin in bins:
    cursor.execute("""
        SELECT weight, created_at FROM waste_levels 
        WHERE bin_id = %s 
        ORDER BY created_at ASC
    """, (bin["id"],))
    levels = cursor.fetchall()

    prev_level = 0
    for row in levels:
        date = row["created_at"].date()
        weekday = date.weekday()
        is_weekend = int(weekday >= 5)
        level = row["weight"]
        overflow = 1 if level >= 80 else 0  # 80kg = overflow (you can adjust)

        bin_data.append({
            'date': date,
            'bin_id': bin["id"],
            'day_of_week': weekday,
            'is_weekend': is_weekend,
            'bin_level': level,
            'overflow': overflow,
            'prev_bin_level': prev_level
        })
        prev_level = level

df = pd.DataFrame(bin_data)

if df.empty:
    print("No data to predict.")
    exit()

# Fill missing
df['prev_overflow'] = df.groupby('bin_id')['overflow'].shift(1).fillna(0)

# Model training
features = ['prev_bin_level', 'day_of_week', 'is_weekend', 'prev_overflow']
X = df[features]
y = df['overflow']
X_train, X_test, y_train, y_test = train_test_split(X, y, shuffle=False, test_size=0.2)

model = RandomForestRegressor(n_estimators=100, random_state=42)
model.fit(X_train, y_train)

# Predict all
df['predicted_overflow'] = model.predict(X)
df['collection_needed'] = df['predicted_overflow'] >= 0.5

# Insert predictions to DB
for _, row in df.iterrows():
    cursor.execute("""
        INSERT INTO waste_predictions (bin_id, date, predicted_overflow, collection_needed, created_at, updated_at)
        VALUES (%s, %s, %s, %s, NOW(), NOW())
        ON DUPLICATE KEY UPDATE 
            predicted_overflow = VALUES(predicted_overflow), 
            collection_needed = VALUES(collection_needed), 
            updated_at = NOW()
    """, (int(row['bin_id']), row['date'], float(row['predicted_overflow']), int(row['collection_needed'])))
db.commit()
print("Predictions saved.")
