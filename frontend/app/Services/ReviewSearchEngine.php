<?php

namespace App\Services;

use Exception;
use Illuminate\Support\Facades\Storage;
use League\Csv\Reader;
use voku\helper\StopWords;
use Illuminate\Support\Str;

class ReviewSearchEngine
{
    private $reviews = [];
    private $vectorizer;
    private $tfidfMatrix;
    private $indexedReviews = [];
    private $stopWords;

    public function __construct()
    {
        // Initialize Indonesian stop words
        $stopWords = new StopWords();
        $this->stopWords = $stopWords->getStopWordsFromLanguage('id');
        
        // Add custom stop words
        $customStopWords = [
            'dana', 'aplikasi', 'app', 'apk', 'e-wallet', 'dompet', 'digital', 
            'uang', 'saldo', 'fitur', 'pakai', 'banget', 'bgt', 'sangat', 
            'sekali', 'udah', 'bikin', 'buat'
        ];
        $this->stopWords = array_merge($this->stopWords, $customStopWords);
    }

    public function loadReviewsFromCsv($filePath)
    {
        try {
            $stream = fopen($filePath, 'r');
            $csv = Reader::createFromStream($stream);
            $csv->setHeaderOffset(0);
            
            $records = $csv->getRecords();
            
            foreach ($records as $record) {
                $this->reviews[] = [
                    'userName' => $record['userName'] ?? '',
                    'score' => $record['score'] ?? '',
                    'at' => $record['at'] ?? '',
                    'content' => $record['content'] ?? '',
                    'replyContent' => $record['replyContent'] ?? '',
                    'reviewId' => $record['reviewId'] ?? ''
                ];
            }
            
            fclose($stream);
            
            return count($this->reviews);
        } catch (Exception $e) {
            throw new Exception("Failed to load CSV: " . $e->getMessage());
        }
    }

    public function initializeSearchEngine()
    {
        if (empty($this->reviews)) {
            throw new Exception("No reviews loaded to index.");
        }

        // Clean and prepare reviews
        $this->indexedReviews = [];
        $documents = [];
        
        foreach ($this->reviews as $review) {
            $cleanedContent = $this->cleanText($review['content']);
            if (!empty($cleanedContent)) {
                $this->indexedReviews[] = $review;
                $documents[] = $cleanedContent;
            }
        }

        if (empty($documents)) {
            throw new Exception("No valid reviews after cleaning.");
        }

        // Create TF-IDF matrix (simplified implementation)
        $this->vectorizer = new TfIdfVectorizer($this->stopWords);
        $this->tfidfMatrix = $this->vectorizer->transform($documents);
    }

    public function search($query, $topN = 5)
    {
        if (empty($query)) {
            return [];
        }

        $cleanedQuery = $this->cleanText($query);
        $queryVector = $this->vectorizer->transformQuery($cleanedQuery);
        
        $scores = [];
        foreach ($this->tfidfMatrix as $i => $docVector) {
            $score = $this->cosineSimilarity($queryVector, $docVector);
            if ($score > 0) {
                $scores[$i] = $score;
            }
        }

        arsort($scores);
        $topIndices = array_slice(array_keys($scores), 0, $topN, true);
        
        $results = [];
        foreach ($topIndices as $index) {
            $review = $this->indexedReviews[$index];
            $review['similarity_score'] = $scores[$index];
            $results[] = $review;
        }

        return $results;
    }

    private function cleanText($text)
    {
        $text = strtolower($text);
        $text = preg_replace('/[^a-z0-9\s]/', '', $text); // Remove special chars
        $text = preg_replace('/\s+/', ' ', $text); // Remove extra spaces
        
        // Remove stop words
        $words = explode(' ', $text);
        $words = array_filter($words, function($word) {
            return !in_array($word, $this->stopWords) && strlen($word) > 2;
        });
        
        return implode(' ', $words);
    }

    private function cosineSimilarity($vecA, $vecB)
    {
        $dotProduct = 0;
        $normA = 0;
        $normB = 0;
        
        foreach ($vecA as $term => $weightA) {
            $weightB = $vecB[$term] ?? 0;
            $dotProduct += $weightA * $weightB;
            $normA += $weightA * $weightA;
        }
        
        foreach ($vecB as $weightB) {
            $normB += $weightB * $weightB;
        }
        
        if ($normA == 0 || $normB == 0) {
            return 0;
        }
        
        return $dotProduct / (sqrt($normA) * sqrt($normB));
    }
}

class TfIdfVectorizer
{
    private $vocab = [];
    private $idf = [];
    private $stopWords = [];

    public function __construct($stopWords)
    {
        $this->stopWords = $stopWords;
    }

    public function fitTransform($documents)
    {
        // Build vocabulary and calculate TF
        $tf = [];
        $docCount = count($documents);
        
        foreach ($documents as $docId => $doc) {
            $terms = explode(' ', $doc);
            $termCounts = array_count_values($terms);
            $docLength = count($terms);
            
            foreach ($termCounts as $term => $count) {
                if (!in_array($term, $this->stopWords)) {
                    if (!isset($this->vocab[$term])) {
                        $this->vocab[$term] = count($this->vocab);
                    }
                    $tf[$docId][$term] = $count / $docLength;
                }
            }
        }
        
        // Calculate IDF
        $docFreq = array_fill_keys(array_keys($this->vocab), 0);
        
        foreach ($documents as $docId => $doc) {
            $terms = array_unique(explode(' ', $doc));
            foreach ($terms as $term) {
                if (isset($this->vocab[$term])) {
                    $docFreq[$term]++;
                }
            }
        }
        
        foreach ($docFreq as $term => $count) {
            $this->idf[$term] = log($docCount / ($count + 1)) + 1;
        }
        
        // Calculate TF-IDF
        $tfidf = [];
        foreach ($tf as $docId => $terms) {
            foreach ($terms as $term => $tfVal) {
                $tfidf[$docId][$term] = $tfVal * $this->idf[$term];
            }
        }
        
        return $tfidf;
    }

    public function transform($documents)
    {
        return $this->fitTransform($documents);
    }

    public function transformQuery($query)
    {
        $terms = explode(' ', $query);
        $termCounts = array_count_values($terms);
        $queryLength = count($terms);
        
        $queryVector = [];
        foreach ($termCounts as $term => $count) {
            if (isset($this->vocab[$term])) {
                $tf = $count / $queryLength;
                $queryVector[$term] = $tf * $this->idf[$term];
            }
        }
        
        return $queryVector;
    }
}