import pandas as pd
from sklearn.model_selection import train_test_split
from sklearn.ensemble import RandomForestClassifier
from sklearn.metrics import accuracy_score, confusion_matrix
import joblib

# 1️⃣ Load CSV
df = pd.read_csv('training_data.csv')

# 2️⃣ Features & labels
X = df.drop(columns=['risk_label'])
y = df['risk_label']

# 3️⃣ Split train/test
X_train, X_test, y_train, y_test = train_test_split(
    X, y, test_size=0.2, random_state=42
)

# 4️⃣ Train Random Forest
clf = RandomForestClassifier(n_estimators=100, random_state=42)
clf.fit(X_train, y_train)

# 5️⃣ Evaluate
y_pred = clf.predict(X_test)
print("Accuracy:", accuracy_score(y_test, y_pred))
print("Confusion Matrix:\n", confusion_matrix(y_test, y_pred))

# 6️⃣ Save model
joblib.dump(clf, 'account_risk_model.pkl')
print("✅ Model saved to model/account_risk_model.pkl")