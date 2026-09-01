from flask import Flask
from flask import request
from flask import jsonify
from flask_cors import CORS

import subprocess
import json

from config import APP_NAME, VERSION

app = Flask(__name__)
CORS(app)

# ---------------------------------------
# HOME
# ---------------------------------------

@app.route("/")

def home():

    return jsonify({

        "system":"QT Cafe Demand Forecasting System",

        "status":"Running"

    })

# ---------------------------------------
# TRAIN MODELS
# ---------------------------------------

@app.route("/train", methods=["POST"])

def train_models():

    try:

        result = subprocess.run(

            ["python", "train_model.py"],

            capture_output=True,

            text=True

        )

        if result.returncode != 0:

            return jsonify({

                "success":False,

                "message":result.stderr

            })

        return jsonify({

            "success":True,

            "message":"Models trained successfully.",

            "output":result.stdout

        })

    except Exception as e:

        return jsonify({

            "success":False,

            "message":str(e)

        })
    
    # ---------------------------------------
# MODEL COMPARISON
# ---------------------------------------

@app.route("/model-comparison", methods=["GET"])

def model_comparison():

    try:

        result = subprocess.run(

            ["python", "model_comparison.py"],

            capture_output=True,

            text=True

        )

        if result.returncode != 0:

            return jsonify({

                "success": False,

                "message": result.stderr

            })

        data = json.loads(result.stdout)

        return jsonify({

            "success": True,

            "data": data

        })

    except Exception as e:

        return jsonify({

            "success": False,

            "message": str(e)

        })

# ---------------------------------------
# PREDICTION
# ---------------------------------------

@app.route("/predict", methods=["POST"])

def predict():

    try:

        result = subprocess.run(

            ["python", "predict.py"],

            capture_output=True,

            text=True

        )

        if result.returncode != 0:

            return jsonify({

                "success": False,

                "message": result.stderr

            })

        return jsonify({

            "success": True,

            "message": "Prediction completed successfully.",

            "output": result.stdout

        })

    except Exception as e:

        return jsonify({

            "success": False,

            "message": str(e)

        })
    
    # ---------------------------------------
# HEALTH CHECK
# ---------------------------------------

@app.route("/health", methods=["GET"])

def health():

    return jsonify({

        "success": True,

        "system": APP_NAME,

        "version": VERSION,

        "status": "Running"

    })

# ---------------------------------------
# START APPLICATION
# ---------------------------------------

if __name__ == "__main__":

    print("------------------------------------")
    print(APP_NAME)
    print("------------------------------------")
    print("Flask API Started")
    print("Waiting for PHP requests...")
    print("------------------------------------")

    app.run(

        host="0.0.0.0",

        port=5000,

        debug=False

    )