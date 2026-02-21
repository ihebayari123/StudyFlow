import pandas as pd
import numpy as np
import random

# Number of fake users
N = 3000

np.random.seed(42)

data = {
    "login_frequency": np.random.randint(0, 20, N),
    "failed_login_attempts": np.random.randint(0, 10, N),
    "time_since_last_login": np.random.randint(0, 90, N),
    "hour_of_login": np.random.randint(0, 24, N),
    "account_age_days": np.random.randint(1, 365, N),
    "role_encoded": np.random.choice([0, 1, 2], N),  # 0=student,1=teacher,2=admin
    "is_weekend": np.random.choice([0, 1], N)
}

df = pd.DataFrame(data)

# Create risk label
df["risk_label"] = 0

df.loc[df["failed_login_attempts"] > 5, "risk_label"] = 1
df.loc[(df["time_since_last_login"] > 30) & (df["failed_login_attempts"] > 2), "risk_label"] = 1
df.loc[(df["hour_of_login"] < 6) & (df["failed_login_attempts"] > 3), "risk_label"] = 1
df.loc[(df["login_frequency"] < 2) & (df["account_age_days"] < 7), "risk_label"] = 1

# Add noise
for i in random.sample(range(N), int(0.05 * N)):
    df.loc[i, "risk_label"] = 1 - df.loc[i, "risk_label"]

# Save CSV
df.to_csv("ml_models/training_data.csv", index=False)

print("✅ training_data.csv created successfully")