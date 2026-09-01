import os
import joblib
import mysql.connector
import pandas as pd

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

print("Connected to database.")

# ---------------------------------------
# GET CURRENT BEST MODEL
# ---------------------------------------

cursor.execute("""

SELECT
model_name

FROM model_results

WHERE is_best_model = 1

LIMIT 1

""")

best_model = cursor.fetchone()

if best_model is None:

    raise Exception(
        "No trained model found. Please train the models first."
    )

model_name = best_model["model_name"]

print(f"Best Model : {model_name}")

# ---------------------------------------
# LOAD MODEL
# ---------------------------------------

model_file = {

    "Decision Tree":"decision_tree.pkl",

    "Random Forest":"random_forest.pkl",

    "Linear Regression":"linear_regression.pkl"

}

selected_model = joblib.load(

    os.path.join(

        MODEL_FOLDER,

        model_file[model_name]

    )

)

print("Machine learning model loaded.")

# ---------------------------------------
# LOAD ENCODERS
# ---------------------------------------

item_encoder = joblib.load(

    os.path.join(

        MODEL_FOLDER,

        "item_encoder.pkl"

    )

)

category_encoder = joblib.load(

    os.path.join(

        MODEL_FOLDER,

        "category_encoder.pkl"

    )

)

print("Encoders loaded.")

# ---------------------------------------
# LOAD LATEST SALES DATA
# ---------------------------------------

query = """

SELECT

sale_date,
item_name,
category,
unit_price

FROM sales_records

ORDER BY sale_date ASC

"""

df = pd.read_sql(query, conn)

if df.empty:

    raise Exception(
        "No sales records available for prediction."
    )

print("Sales records loaded.")

# ---------------------------------------
# FIND FORECAST MONTH
# ---------------------------------------

latest_date = pd.to_datetime(
    df["sale_date"].max()
)

forecast_date = latest_date + pd.DateOffset(months=1)

forecast_month = forecast_date.month

forecast_year = forecast_date.year

print(
    f"Forecast Period : {forecast_date.strftime('%B %Y')}"
)

# ---------------------------------------
# GET LATEST MENU ITEMS
# ---------------------------------------

menu = df[
    [
        "item_name",
        "category",
        "unit_price"
    ]
].drop_duplicates()

print("Menu list prepared.")

# ---------------------------------------
# ENCODE MENU DATA
# ---------------------------------------

menu["item_encoded"] = item_encoder.transform(
    menu["item_name"]
)

menu["category_encoded"] = category_encoder.transform(
    menu["category"]
)

print("Encoding completed.")

# ---------------------------------------
# PREPARE PREDICTION DATA
# ---------------------------------------

prediction_data = pd.DataFrame({

    "month": forecast_month,

    "year": forecast_year,

    "day": 1,

    "unit_price": menu["unit_price"],

    "item_encoded": menu["item_encoded"],

    "category_encoded": menu["category_encoded"]

})

print("Prediction dataset created.")

# ---------------------------------------
# GENERATE PREDICTIONS
# ---------------------------------------

menu["predicted_quantity"] = selected_model.predict(
    prediction_data
)

menu["predicted_quantity"] = (
    menu["predicted_quantity"]
    .round()
    .astype(int)
)

menu["predicted_quantity"] = (
    menu["predicted_quantity"]
    .clip(lower=0)
)

print("Prediction completed.")

# ---------------------------------------
# PREPARE RESULT TABLE
# ---------------------------------------

prediction_result = menu[

    [

        "item_name",

        "category",

        "predicted_quantity"

    ]

].copy()

prediction_result = prediction_result.sort_values(

    by="predicted_quantity",

    ascending=False

)

print("Prediction table prepared.")

# ---------------------------------------
# CLEAR PREVIOUS FORECAST
# ---------------------------------------

cursor.execute("DELETE FROM forecast_results")

conn.commit()

print("Previous forecast removed.")

# ---------------------------------------
# SAVE FORECAST
# ---------------------------------------

for _, row in prediction_result.iterrows():

    cursor.execute("""

    INSERT INTO forecast_results
    (

        menu_item_id,

        item_name,

        forecast_month,

        predicted_quantity,

        model_used

    )

    VALUES
    (

        (
            SELECT id
            FROM menu_items
            WHERE item_name=%s
            LIMIT 1
        ),

        %s,

        %s,

        %s,

        %s

    )

    """,

    (

        row["item_name"],

        row["item_name"],

        forecast_date.strftime("%B %Y"),

        int(row["predicted_quantity"]),

        model_name

    )

    )

conn.commit()

print("Forecast saved.")

# ---------------------------------------
# DISPLAY RESULT
# ---------------------------------------

print("--------------------------------")

print("DEMAND FORECAST COMPLETED")

print("--------------------------------")

print(f"Forecast Period : {forecast_date.strftime('%B %Y')}")

print(f"Model Used : {model_name}")

print()

print(prediction_result)

cursor.close()

conn.close()

print()

print("Prediction completed successfully.")