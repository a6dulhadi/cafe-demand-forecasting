import sys
import mysql.connector

from config import *

# ---------------------------------------
# CONNECT DATABASE
# ---------------------------------------

conn = mysql.connector.connect(
    host=DB_HOST,
    user=DB_USER,
    password=DB_PASSWORD,
    database=DB_NAME
)

cursor = conn.cursor(dictionary=True)

print("Connected to database.", file=sys.stderr)

# ---------------------------------------
# GET LATEST TRAINING STATUS
# ---------------------------------------

cursor.execute("""

SELECT *

FROM training_history

ORDER BY training_date DESC

LIMIT 1

""")

training_status = cursor.fetchone()

print("Training status loaded.", file=sys.stderr)

# ---------------------------------------
# GET MODEL COMPARISON
# ---------------------------------------

cursor.execute("""

SELECT

model_name,
mae,
rmse,
r2_score,
is_best_model

FROM model_results

ORDER BY
is_best_model DESC,
mae ASC

""")

comparison = cursor.fetchall()

print("Model comparison loaded.", file=sys.stderr)

# ---------------------------------------
# GET TRAINING HISTORY
# ---------------------------------------

cursor.execute("""

SELECT

training_date,
best_model,
total_records

FROM training_history

ORDER BY training_date DESC

LIMIT 10

""")

history = cursor.fetchall()

print("Training history loaded.", file=sys.stderr)

# ---------------------------------------
# DEFAULT STATUS
# ---------------------------------------

if training_status is None:

    training_status = {

        "training_date": "-",

        "total_records": 0,

        "training_records": 0,

        "testing_records": 0,

        "best_model": "-",

        "recommendation":
        "Please upload sales data and train the machine learning models."

    }

print("Training status checked.", file=sys.stderr)

# ---------------------------------------
# PREPARE RESPONSE
# ---------------------------------------

response = {

    "status": training_status,

    "comparison": comparison,

    "history": history

}

print("Model comparison ready.", file=sys.stderr)

# ---------------------------------------
# CLOSE DATABASE
# ---------------------------------------

cursor.close()

conn.close()

print("Database connection closed.", file=sys.stderr)

# ---------------------------------------
# RETURN RESPONSE
# ---------------------------------------

if __name__ == "__main__":

    import json

    print(

        json.dumps(

            response,

            indent=4,

            default=str

        )

    )