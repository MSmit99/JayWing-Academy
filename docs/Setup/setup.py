# setup.py
import subprocess
import sys
def version_check():
    if not (sys.version_info.major == 3 and 10 <= sys.version_info.minor <= 12):
        sys.exit("ERROR: This application requires Python 3.10–3.12. "
                "Please downgrade your Python version.")

def install_requirements():
    subprocess.check_call([sys.executable, "-m", "pip", "install", "-r", "requirements.txt"])

def run_postinstall():
    subprocess.check_call([sys.executable, "postinstall.py"])

if __name__ == "__main__":
    version_check()
    install_requirements()
    run_postinstall()
