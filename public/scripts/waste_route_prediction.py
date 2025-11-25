# waste_route_prediction.py
import pandas as pd
import pymysql
from sklearn.ensemble import RandomForestClassifier
import datetime
import json

# --- MySQL Connection ---
conn = pymysql.connect(
    host='127.0.0.1',
    user='root',
    password='',
    database='thesis_app',
    cursorclass=pymysql.cursors.DictCursor
)

# --- Load Waste Data for last 30 days ---
query = """
SELECT bin_id, weight, level, created_at
FROM waste_levels
WHERE created_at >= CURDATE() - INTERVAL 30 DAY
ORDER BY bin_id, created_at
"""
df = pd.read_sql(query, conn)

# Feature Engineering
df['hour'] = df['created_at'].dt.hour
df['day'] = df['created_at'].dt.dayofweek
df['overflow'] = df['level'] >= 80  # overflow threshold

# Use last known weight/level for prediction
df['prev_weight'] = df.groupby('bin_id')['weight'].shift(1).fillna(0)
df['prev_level'] = df.groupby('bin_id')['level'].shift(1).fillna(0)

features = ['prev_weight', 'prev_level', 'hour', 'day']
X = df[features]
y = df['overflow']

# Train Random Forest
model = RandomForestClassifier(n_estimators=100, random_state=42)
model.fit(X, y)

# Predict for next day
next_day = datetime.datetime.now() + datetime.timedelta(days=1)
recommendations = []

for bin_id in df['bin_id'].unique():
    last_row = df[df['bin_id'] == bin_id].iloc[-1]
    X_pred = pd.DataFrame([{
        'prev_weight': last_row['weight'],
        'prev_level': last_row['level'],
        'hour': next_day.hour,
        'day': next_day.weekday()
    }])
    pred_prob = model.predict_proba(X_pred)[0][1]
    recommendations.append({
        'bin_id': bin_id,
        'date': next_day.strftime('%Y-%m-%d'),
        'predicted_overflow': float(pred_prob),
        'collection_needed': pred_prob > 0.7  # threshold
    })

# Sort by highest probability first (priority for collection)
recommendations.sort(key=lambda x: x['predicted_overflow'], reverse=True)

# Output JSON
print(json.dumps(recommendations))
