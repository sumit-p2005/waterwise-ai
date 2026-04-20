# WaterWise AI (XAMPP)

## Run Steps
1. Copy project to `C:\xampp\htdocs\waterwise-ai`
2. Start Apache + MySQL in XAMPP
3. Import `database/waterwise_ai.sql` in phpMyAdmin
4. Open `http://localhost/waterwise-ai/import_dataset.php` once to load `assets/data/Results_MADE.csv`
5. Open `http://localhost/waterwise-ai`

## Notes
- KNN prediction is fully local (`api/predict.php`) and uses table `dataset_data`.
- Chat is dataset/rule based (`api/chat.php`) and uses no external AI API.
- CSV mapping used for model compatibility:
  - `Conductivity (mho/ Cm)` -> `tds` proxy
  - `Bio-Chemical Oxygen Demand (mg/L)` -> `turbidity` proxy
- PDF report endpoint: `report.php`.
