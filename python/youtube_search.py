#!/usr/bin/env python
import os
import sys
import json
from googleapiclient.discovery import build

def youtube_search(query):
    try:
        # Retrieve the YouTube API key from an environment variable for security
        API_KEY = os.getenv('YOUTUBE_API_KEY')
        if not API_KEY:
            raise ValueError("YOUTUBE_API_KEY not set in environment variables.")
        
        API_SERVICE_NAME = 'youtube'
        API_VERSION = 'v3'
        
        # Initialize YouTube API client
        youtube = build(API_SERVICE_NAME, API_VERSION, developerKey=API_KEY)
        
        # Perform the search request
        request = youtube.search().list(
            part='snippet',
            q=query,
            type='video',
            maxResults=10
        )
        response = request.execute()

        # Process the response and extract video details
        results = []
        for item in response.get('items', []):
            video_data = {
                'title': item['snippet']['title'],
                'url': f"https://www.youtube.com/watch?v={item['id']['videoId']}",
                'description': item['snippet']['description']
            }
            results.append(video_data)

        # Output JSON directly to stdout
        print(json.dumps({"videos": results}))
    
    except Exception as e:
        # Output error as JSON
        print(json.dumps({"error": str(e)}))

def main():
    if len(sys.argv) > 1:
        query = sys.argv[1]
        youtube_search(query)
    else:
        # Output error as JSON for missing search term
        print(json.dumps({"error": "Please provide a search term."}))

if __name__ == "__main__":
    main()
