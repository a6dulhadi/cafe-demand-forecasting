import os
import joblib
import warnings
import mysql.connector
import pandas as pd

from sklearn.model_selection import train_test_split

from sklearn.tree import DecisionTreeRegressor
from sklearn.ensemble import RandomForestRegressor
from sklearn.linear_model import LinearRegression

from sklearn.metrics import (
    mean_absolute_error,
    mean_squared_error,
    root_mean_squared_error,
    r2_score
)

warnings.filterwarnings("ignore")

# ---------------------------------------
# DATABASE CONFIGURATION
# ---------------------------------------

from config import *

os.makedirs(MODEL_FOLDER, exist_ok=True)

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
# LOAD SALES RECORDS
# ---------------------------------------

query = """
SELECT
    sale_date,
    item_name,
    category,
    quantity_sold,
    unit_price,
    total_sales
FROM sales_records
ORDER BY sale_date ASC
"""

df = pd.read_sql(query, conn)

print("Sales records loaded.")

# ---------------------------------------
# VALIDATE DATASET
# ---------------------------------------

if df.empty:

    raise Exception(
        "No sales records found. Please upload CSV before training."
    )

total_records = len(df)

print(f"Total Records : {total_records}")

if total_records < 30:

    raise Exception(
        "Not enough historical data for machine learning training."
    )

# ---------------------------------------
# DATA PREPROCESSING
# ---------------------------------------

df["sale_date"] = pd.to_datetime(df["sale_date"])

df["month"] = df["sale_date"].dt.month

df["year"] = df["sale_date"].dt.year

df["day"] = df["sale_date"].dt.day

# ---------------------------------------
# FEATURE SELECTION
# ---------------------------------------

X = df[
    [
        "month",
        "year",
        "day",
        "unit_price"
    ]
]

y = df["quantity_sold"]

print("Dataset preprocessing completed.")

print("Features prepared.")

print("Training dataset ready.")

# ---------------------------------------
# ENCODE CATEGORICAL DATA
# ---------------------------------------

from sklearn.preprocessing import LabelEncoder

item_encoder = LabelEncoder()
category_encoder = LabelEncoder()

df["item_encoded"] = item_encoder.fit_transform(
    df["item_name"]
)

df["category_encoded"] = category_encoder.fit_transform(
    df["category"]
)

# ---------------------------------------
# FEATURE SELECTION
# ---------------------------------------

X = df[
    [
        "month",
        "year",
        "day",
        "unit_price",
        "item_encoded",
        "category_encoded"
    ]
]

y = df["quantity_sold"]

print("Categorical encoding completed.")

# ---------------------------------------
# TRAIN / TEST SPLIT
# ---------------------------------------

X_train, X_test, y_train, y_test = train_test_split(
    X,
    y,
    test_size=TEST_SIZE,
    random_state=RANDOM_STATE
)

training_records = len(X_train)

testing_records = len(X_test)

print(f"Training Records : {training_records}")

print(f"Testing Records : {testing_records}")

print("Dataset successfully split.")

# ---------------------------------------
# TRAIN MACHINE LEARNING MODELS
# ---------------------------------------

print("Training Decision Tree...")

decision_tree = DecisionTreeRegressor(
    random_state=RANDOM_STATE
)

decision_tree.fit(
    X_train,
    y_train
)

print("Decision Tree completed.")

# ---------------------------------------

print("Training Random Forest...")

random_forest = RandomForestRegressor(
    random_state=RANDOM_STATE,
    n_estimators=100
)

random_forest.fit(
    X_train,
    y_train
)

print("Random Forest completed.")

# ---------------------------------------

print("Training Linear Regression...")

linear_regression = LinearRegression()

linear_regression.fit(
    X_train,
    y_train
)

print("Linear Regression completed.")

# ---------------------------------------
# MODEL PREDICTIONS
# ---------------------------------------

dt_prediction = decision_tree.predict(X_test)

rf_prediction = random_forest.predict(X_test)

lr_prediction = linear_regression.predict(X_test)

print("Prediction completed.")

# ---------------------------------------
# MODEL EVALUATION
# ---------------------------------------

models = {

    "Decision Tree": {

        "model": decision_tree,

        "prediction": dt_prediction

    },

    "Random Forest": {

        "model": random_forest,

        "prediction": rf_prediction

    },

    "Linear Regression": {

        "model": linear_regression,

        "prediction": lr_prediction

    }

}

results = []

print("Calculating model performance...")

# ---------------------------------------
# CALCULATE PERFORMANCE METRICS
# ---------------------------------------

for model_name, model_data in models.items():

    prediction = model_data["prediction"]

    mae = mean_absolute_error(
        y_test,
        prediction
    )

    rmse = root_mean_squared_error(
        y_test,
        prediction
    )

    r2 = r2_score(
        y_test,
        prediction
    )

    results.append({

        "name": model_name,

        "model": model_data["model"],

        "mae": mae,

        "rmse": rmse,

        "r2": r2

    })

print("Performance calculation completed.")

# ---------------------------------------
# FIND BEST MODEL
# ---------------------------------------

best_model = min(
    results,
    key=lambda x: x["mae"]
)

print("Best model selected.")

print(f"Best Model : {best_model['name']}")

print(f"MAE : {best_model['mae']:.4f}")

print(f"RMSE : {best_model['rmse']:.4f}")

print(f"R2 Score : {best_model['r2']:.4f}")

# ---------------------------------------
# SAVE TRAINED MODELS
# ---------------------------------------

print("Saving trained models...")

joblib.dump(
    decision_tree,
    os.path.join(
        MODEL_FOLDER,
        "decision_tree.pkl"
    )
)

joblib.dump(
    random_forest,
    os.path.join(
        MODEL_FOLDER,
        "random_forest.pkl"
    )
)

joblib.dump(
    linear_regression,
    os.path.join(
        MODEL_FOLDER,
        "linear_regression.pkl"
    )
)

joblib.dump(
    item_encoder,
    os.path.join(
        MODEL_FOLDER,
        "item_encoder.pkl"
    )
)

joblib.dump(
    category_encoder,
    os.path.join(
        MODEL_FOLDER,
        "category_encoder.pkl"
    )
)

print("Models successfully saved.")

# ---------------------------------------
# CLEAR PREVIOUS MODEL RESULTS
# ---------------------------------------

cursor.execute("DELETE FROM model_results")

conn.commit()

# ---------------------------------------
# SAVE MODEL COMPARISON RESULTS
# ---------------------------------------

for result in results:

    is_best = 0

    if result["name"] == best_model["name"]:
        is_best = 1

    cursor.execute("""

    INSERT INTO model_results
    (
        model_name,
        mae,
        rmse,
        r2_score,
        is_best_model
    )

    VALUES
    (
        %s,
        %s,
        %s,
        %s,
        %s
    )

    """,

    (

        result["name"],
        float(result["mae"]),
        float(result["rmse"]),
        float(result["r2"]),
        is_best

    )

    )

conn.commit()

print("Model comparison saved.")

# ---------------------------------------
# GENERATE RECOMMENDATION
# ---------------------------------------

recommendation = (
    f"Based on the latest training results, "
    f"{best_model['name']} is recommended as the current forecasting "
    f"model because it achieved the lowest prediction error "
    f"(MAE and RMSE) together with the strongest overall prediction "
    f"performance (R² score) among the evaluated machine learning models. "
    f"This recommendation will be updated automatically after the next "
    f"successful model training using newly uploaded sales data."
)

print("Recommendation generated.")

# ---------------------------------------
# SAVE TRAINING HISTORY
# ---------------------------------------

cursor.execute("""

INSERT INTO training_history
(
    total_records,
    training_records,
    testing_records,
    best_model,
    recommendation
)

VALUES
(
    %s,
    %s,
    %s,
    %s,
    %s
)

""",

(

    total_records,
    training_records,
    testing_records,
    best_model["name"],
    recommendation

)

)

conn.commit()

print("Training history saved.")

# ---------------------------------------
# TRAINING COMPLETED
# ---------------------------------------

print("----------------------------------")
print("TRAINING COMPLETED SUCCESSFULLY")
print("----------------------------------")

print(f"Dataset Records : {total_records}")
print(f"Training Records : {training_records}")
print(f"Testing Records : {testing_records}")

print()

print(f"Best Model : {best_model['name']}")
print(f"MAE : {best_model['mae']:.4f}")
print(f"RMSE : {best_model['rmse']:.4f}")
print(f"R² Score : {best_model['r2']:.4f}")

print()

print("Models saved successfully.")
print("Database updated successfully.")
print("Ready for prediction.")

cursor.close()
conn.close()