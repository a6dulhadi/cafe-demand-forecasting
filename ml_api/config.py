"""
QT Cafe Demand Forecasting System
Configuration File

This file stores all project configuration.
Only this file needs to be changed when deploying
to another server (e.g. PythonAnywhere).
"""

# ----------------------------------------
# DATABASE CONFIGURATION
# ----------------------------------------

DB_HOST = "127.0.0.1"
DB_USER = "root"
DB_PASSWORD = ""
DB_NAME = "cafe_forecasting_db"
DB_PORT = 3306

# ----------------------------------------
# MACHINE LEARNING SETTINGS
# ----------------------------------------

TEST_SIZE = 0.20
RANDOM_STATE = 42

# ----------------------------------------
# MODEL SAVE LOCATION
# ----------------------------------------

MODEL_FOLDER = "saved_models"

# ----------------------------------------
# TRAINING SETTINGS
# ----------------------------------------

ENABLE_MODEL_SAVE = True

ENABLE_DATABASE_SAVE = True

ENABLE_TRAINING_HISTORY = True

# ----------------------------------------
# APPLICATION SETTINGS
# ----------------------------------------

APP_NAME = "QT Cafe Demand Forecasting System"

VERSION = "1.0"