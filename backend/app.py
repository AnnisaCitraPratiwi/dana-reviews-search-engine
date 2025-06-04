from flask import Flask, request, jsonify
from flask_cors import CORS
import pandas as pd
import numpy as np
from sklearn.feature_extraction.text import TfidfVectorizer
from sklearn.metrics.pairwise import cosine_similarity
import re
import pickle
import os

app = Flask(__name__)
CORS(app)  # Enable CORS for Laravel frontend

class ReviewSearchEngine:
    def __init__(self):
        self.reviews_df = None
        self.tfidf_vectorizer = None
        self.tfidf_matrix = None
        self.load_data()
    
    def preprocess_text(self, text):
        """Preprocessing text untuk TF-IDF"""
        if pd.isna(text):
            return ""
        
        # Convert to lowercase
        text = str(text).lower()
        
        # Remove special characters, keep only letters and spaces
        text = re.sub(r'[^a-zA-Z\s]', '', text)
        
        # Remove extra whitespaces
        text = ' '.join(text.split())
        
        return text
    
    def load_data(self):
        """Load dataset dan build TF-IDF matrix"""
        try:
            # Load dataset - sesuaikan path dengan lokasi file Anda
            if os.path.exists('dana_reviews.csv'):
                self.reviews_df = pd.read_csv('dana_reviews.csv')
            elif os.path.exists('reviews_dataset.csv'):
                self.reviews_df = pd.read_csv('reviews_dataset.csv')
            else:
                # Sample data jika file tidak ada dengan struktur yang sesuai
                self.reviews_df = pd.DataFrame({
                    'userName': ['User1', 'User2', 'User3', 'User4', 'User5'],
                    'score': [5, 4, 5, 4, 5],
                    'at': ['2024-01-01', '2024-01-02', '2024-01-03', '2024-01-04', '2024-01-05'],
                    'content': [
                        'Aplikasi dana sangat mudah digunakan untuk transfer uang',
                        'DANA membantu saya bayar tagihan dengan cepat',
                        'Fitur scan QR code nya bagus dan akurat',
                        'Promo cashback dana selalu menarik',
                        'Customer service dana responsif dan membantu'
                    ],
                    'replyContent': ['', '', '', '', ''],
                    'reviewId': ['R001', 'R002', 'R003', 'R004', 'R005']
                })
            
            # Bersihkan data yang kosong atau null
            self.reviews_df = self.reviews_df.dropna(subset=['content'])
            self.reviews_df = self.reviews_df[self.reviews_df['content'].str.strip() != '']
            
            # Preprocessing - gunakan kolom 'content' sebagai review text
            self.reviews_df['processed_review'] = self.reviews_df['content'].apply(self.preprocess_text)
            
            # Build TF-IDF matrix dengan stopwords bahasa Indonesia
            indo_stopwords = [
                'yang', 'untuk', 'pada', 'ke', 'dari', 'dengan', 'di', 'dan', 'ini', 'itu',
                'adalah', 'akan', 'atau', 'bisa', 'saat', 'saya', 'kami', 'kita', 'mereka',
                'nya', 'tidak', 'sudah', 'telah', 'juga', 'masih', 'lagi', 'pun', 'saja',
                'seperti', 'terlalu', 'lebih', 'kurang', 'begitu', 'begitu', 'maka', 'oleh',
                'karena', 'namun', 'tetapi', 'walaupun', 'meskipun', 'agar', 'supaya',
                'setelah', 'sebelum', 'saat', 'ketika', 'jika', 'kalau', 'bila', 'apakah',
                'bagaimana', 'mengapa', 'dimana', 'kapan', 'siapa', 'apa', 'begitu', 'cuma',
                'doang', 'emang', 'ga', 'gak', 'kan', 'kok', 'loh', 'nih', 'sih', 'udah',
                'punya', 'harus', 'banyak', 'sedikit', 'sering', 'jarang', 'selalu', 'pernah',
                'tidak', 'bukan', 'jangan', 'belum', 'sampai', 'sejak', 'segera', 'sekali',
                'selain', 'seluruh', 'sementara', 'semua', 'sendiri', 'seolah', 'sepanjang',
                'seperlunya', 'seperti', 'sepertinya', 'sesudah', 'sesuatu', 'setelah',
                'setiap', 'setidaknya', 'siapapun', 'sini', 'situ', 'suatu', 'tanpa', 'tapi',
                'tiap', 'untuk', 'usah', 'wah', 'yakni', 'yaitu', 'zaman', 'tanpa', 'tapi',
                'tiap', 'untuk', 'usah', 'wah', 'yakni', 'yaitu', 'zaman', 'dana', 'aplikasi',
                'app', 'apk', 'e-wallet', 'dompet', 'digital', 'uang', 'saldo', 'fitur', 'pakai',
                'banget', 'banget', 'bgt', 'bgt', 'sangat', 'sekali', 'udah', 'bikin', 'buat'
            ]
            
            self.tfidf_vectorizer = TfidfVectorizer(
                max_features=5000,
                stop_words=indo_stopwords,
                ngram_range=(1, 2),
                min_df=1,  # Minimum document frequency
                max_df=0.95  # Maximum document frequency
            )
            
            self.tfidf_matrix = self.tfidf_vectorizer.fit_transform(self.reviews_df['processed_review'])
            
            print(f"Data loaded successfully! Total reviews: {len(self.reviews_df)}")
            print(f"Features extracted: {self.tfidf_matrix.shape[1]}")
            
        except Exception as e:
            print(f"Error loading data: {str(e)}")
            # Fallback to sample data dengan struktur yang benar
            self.reviews_df = pd.DataFrame({
                'userName': ['Sample User'],
                'score': [5],
                'at': ['2024-01-01'],
                'content': ['Sample review data'],
                'replyContent': [''],
                'reviewId': ['SAMPLE001']
            })
    
    def search_reviews(self, query, top_k=10):
        """Search reviews berdasarkan query"""
        try:
            if self.tfidf_matrix is None:
                return []
            
            # Preprocess query
            processed_query = self.preprocess_text(query)
            
            # Transform query to TF-IDF vector
            query_vector = self.tfidf_vectorizer.transform([processed_query])
            
            # Calculate cosine similarity
            similarities = cosine_similarity(query_vector, self.tfidf_matrix).flatten()
            
            # Get top similar reviews
            top_indices = similarities.argsort()[-top_k:][::-1]
            
            results = []
            for idx in top_indices:
                if similarities[idx] > 0:  # Only include relevant results
                    row = self.reviews_df.iloc[idx]
                    results.append({
                        'review_text': row['content'],
                        'rating': int(row['score']) if pd.notna(row['score']) else 5,
                        'user': row['userName'] if pd.notna(row['userName']) else 'Anonymous',
                        'date': row['at'] if pd.notna(row['at']) else '',
                        'reply_content': row['replyContent'] if pd.notna(row['replyContent']) and row['replyContent'].strip() else '',
                        'review_id': row['reviewId'] if pd.notna(row['reviewId']) else '',
                        'similarity_score': float(similarities[idx])
                    })
            
            return results
            
        except Exception as e:
            print(f"Error in search: {str(e)}")
            return []

# Initialize search engine
search_engine = ReviewSearchEngine()

@app.route('/api/search', methods=['POST'])
def search_reviews():
    try:
        data = request.get_json()
        query = data.get('query', '')
        limit = data.get('limit', 10)
        
        if not query:
            return jsonify({
                'success': False,
                'message': 'Query is required'
            }), 400
        
        results = search_engine.search_reviews(query, top_k=limit)
        
        return jsonify({
            'success': True,
            'query': query,
            'total_results': len(results),
            'results': results
        })
        
    except Exception as e:
        return jsonify({
            'success': False,
            'message': f'Error: {str(e)}'
        }), 500

@app.route('/api/stats', methods=['GET'])
def get_stats():
    """Get dataset statistics"""
    try:
        if search_engine.reviews_df is not None:
            total_reviews = len(search_engine.reviews_df)
            avg_rating = search_engine.reviews_df['score'].mean() if 'score' in search_engine.reviews_df.columns else 0
            
            # Statistik tambahan
            rating_distribution = search_engine.reviews_df['score'].value_counts().sort_index().to_dict() if 'score' in search_engine.reviews_df.columns else {}
            
            return jsonify({
                'success': True,
                'stats': {
                    'total_reviews': total_reviews,
                    'average_rating': round(float(avg_rating), 2),
                    'rating_distribution': rating_distribution,
                    'has_replies': int(search_engine.reviews_df['replyContent'].notna().sum()) if 'replyContent' in search_engine.reviews_df.columns else 0
                }
            })
        else:
            return jsonify({
                'success': False,
                'message': 'No data available'
            }), 404
            
    except Exception as e:
        return jsonify({
            'success': False,
            'message': f'Error: {str(e)}'
        }), 500

@app.route('/api/health', methods=['GET'])
def health_check():
    return jsonify({
        'success': True,
        'message': 'API is running',
        'status': 'healthy'
    })

if __name__ == '__main__':
    print("Starting DANA Reviews Search API...")
    app.run(debug=True, host='0.0.0.0', port=5000)