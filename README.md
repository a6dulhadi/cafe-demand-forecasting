
# Cafe Menu Demand Forecasting System

A web-based decision support system developed in collaboration 
with a local cafe business to help owners forecast menu item 
demand using machine learning, reducing waste and supporting 
data-driven business decisions.

## Tech Stack
- Python, Flask, scikit-learn, pandas
- PHP, MySQL, JavaScript, HTML/CSS

## Features
- ML model training from real cafe sales data (CSV upload)
- Compares three algorithms — Linear Regression, Decision Tree 
  and Random Forest
- Automated best model selection based on MAE, RMSE and R²
- Demand prediction and ingredient estimation
- Three user roles — Admin, Owner, Staff
- Exportable reports and analytics dashboard
- Role-based dashboards with sales history and trends

## How It Works
1. Owner uploads sales data via CSV
2. Python ML API trains all three models automatically
3. System evaluates and selects the best performing model
4. System predicts future menu demand per item
5. Staff views ingredient estimates based on predictions
6. Admin exports reports and monitors overall performance

## Model
The trained model files (.pkl) are excluded due to file size.
To regenerate:
1. Run `python train_model.py`
2. Model files will be saved automatically to the `/ml_api` folder

## Project Type
Final Year Project (FYP) — Bachelor of Computer Science  
Universiti Selangor (UNISEL), 2026

## Client
Developed in collaboration with a local cafe business as an 
actual client
