import os
import requests
from github import Github

# Get GitHub token, OpenAI API key, and branch name from environment variables
GITHUB_TOKEN = os.getenv('GITHUB_TOKEN')
OPENAI_API_KEY = os.getenv('OPENAI_API_KEY')
BRANCH_NAME = os.getenv('GITHUB_REF').split('/')[-1]

print(f"Running script on branch: {BRANCH_NAME}")

# Initialize GitHub client
g = Github(GITHUB_TOKEN)

# Get the repository
repo = g.get_repo("yourusername/yourrepository")

# Get the list of files in the repository
contents = repo.get_contents("")

# Download files
files = {}
for content_file in contents:
    file_content = repo.get_contents(content_file.path)
    files[content_file.path] = file_content.decoded_content.decode('utf-8')

# Upload files to OpenAI API
url = "https://api.openai.com/v1/files"
headers = {
    "Authorization": f"Bearer {OPENAI_API_KEY}",
    "Content-Type": "application/json"
}
for file_name, file_content in files.items():
    data = {
        "purpose": "fine-tune",
        "file": (file_name, file_content)
    }
    response = requests.post(url, headers=headers, files=data)
    if response.status_code == 200:
        print(f"Uploaded {file_name} successfully")
    else:
        print(f"Failed to upload {file_name}: {response.text}")
