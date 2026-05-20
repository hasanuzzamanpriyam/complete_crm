<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * ATS Parser Library
 * Extracts text from resume files (PDF/DOCX) and calculates ATS match scores
 * against job-required skills.
 */
class ATS_Parser
{
    private $CI;

    /**
     * Skill variant mappings for fuzzy matching
     * Maps common abbreviations/variants to canonical skill names
     */
    private $skill_variants = [
        'javascript' => ['js', 'ecmascript', 'es6', 'es2015', 'es2016', 'es2017', 'typescript', 'ts', 'node.js', 'nodejs'],
        'php'        => ['php7', 'php8', 'php5', 'zend'],
        'mysql'      => ['sql', 'mariadb', 'postgresql', 'postgres', 'dbms', 'relational database'],
        'codeigniter' => ['ci', 'ci3', 'ci4'],
        'laravel'    => ['eloquent', 'blade', 'artisan', 'livewire'],
        'react'      => ['react.js', 'reactjs', 'jsx', 'next.js', 'nextjs', 'redux', 'react native'],
        'node.js'    => ['nodejs', 'express.js', 'expressjs', 'npm', 'npx'],
        'python'     => ['py', 'django', 'flask', 'fastapi', 'pandas', 'numpy'],
        'html/css'   => ['html5', 'css3', 'html', 'css', 'sass', 'scss', 'less', 'bootstrap', 'tailwind'],
        'git'        => ['github', 'gitlab', 'bitbucket', 'version control', 'vcs', 'svn', 'mercurial'],
        'docker'     => ['container', 'containers', 'dockerfile', 'docker compose', 'kubernetes', 'k8s'],
        'aws'        => ['amazon web services', 'ec2', 's3', 'lambda', 'cloudfront', 'rds', 'dynamodb'],
        'rest api'   => ['restful', 'restful api', 'api design', 'graphql', 'soap', 'web services'],
        'agile/scrum' => ['agile', 'scrum', 'kanban', 'sprint', 'jira', 'confluence'],
        'communication' => ['verbal communication', 'written communication', 'presentation', 'public speaking'],
        'team leadership' => ['leadership', 'team lead', 'team management', 'mentoring', 'coaching'],
        'problem solving' => ['analytical', 'critical thinking', 'troubleshooting', 'debugging'],
        'project management' => ['project manager', 'pm', 'ms project', 'asana', 'trello', 'basecamp'],
        'english'    => ['fluent english', 'native english', 'english language', 'bilingual'],
    ];

    /**
     * Common stop words to filter out during tokenization
     */
    private $stop_words = [
        'the', 'and', 'is', 'at', 'which', 'on', 'a', 'an', 'to', 'for', 'with',
        'in', 'of', 'by', 'from', 'or', 'but', 'not', 'are', 'was', 'were', 'be',
        'been', 'being', 'have', 'has', 'had', 'do', 'does', 'did', 'will', 'would',
        'could', 'should', 'may', 'might', 'shall', 'can', 'need', 'dare', 'ought',
        'used', 'it', 'its', 'this', 'that', 'these', 'those', 'i', 'me', 'my',
        'we', 'our', 'you', 'your', 'he', 'him', 'his', 'she', 'her', 'they',
        'them', 'their', 'what', 'which', 'who', 'whom', 'whose', 'where', 'when',
        'why', 'how', 'all', 'each', 'every', 'both', 'few', 'more', 'most',
        'other', 'some', 'such', 'no', 'nor', 'only', 'own', 'same', 'so',
        'than', 'too', 'very', 'just', 'also', 'now', 'here', 'there', 'then',
        'once', 'if', 'because', 'as', 'until', 'while', 'about', 'between',
        'through', 'during', 'before', 'after', 'above', 'below', 'up', 'down',
        'out', 'off', 'over', 'under', 'again', 'further', 'into', 'against',
        'am', 'Mr', 'Mrs', 'Ms', 'Dr', 'Prof', 'etc', 'eg', 'ie',
        'experience', 'years', 'year', 'month', 'months', 'day', 'days',
        'work', 'worked', 'working', 'works', 'responsibilities', 'responsible',
        'including', 'include', 'includes', 'included', 'skills', 'skill',
        'proficient', 'proficiency', 'knowledge', 'knowledgeable', 'familiar',
        'ability', 'abilities', 'able', 'capability', 'capabilities',
        'strong', 'good', 'excellent', 'extensive', 'solid', 'deep',
        'team', 'company', 'companies', 'organization', 'organizations',
        'project', 'projects', 'developed', 'developing', 'development',
        'created', 'creating', 'creation', 'designed', 'designing', 'design',
        'implemented', 'implementing', 'implementation', 'managed', 'managing',
        'management', 'maintained', 'maintaining', 'maintenance', 'built',
        'building', 'build', 'tested', 'testing', 'test', 'deployed',
        'deploying', 'deployment', 'using', 'use', 'utilized', 'utilizing',
        'collaborated', 'collaborating', 'collaboration', 'participated',
        'participating', 'participation', 'contributed', 'contributing',
        'contribution', 'delivered', 'delivering', 'delivery', 'ensured',
        'ensuring', 'provided', 'providing', 'support', 'supported',
        'supporting', 'assisted', 'assisting', 'assistance', 'helped',
        'helping', 'help', 'improved', 'improving', 'improvement',
        'increased', 'increasing', 'increase', 'reduced', 'reducing',
        'reduce', 'optimized', 'optimizing', 'optimization', 'enhanced',
        'enhancing', 'enhancement', 'improve', 'increase', 'reduce'
    ];

    public function __construct()
    {
        $this->CI =& get_instance();

        // Auto-load composer dependencies
        if (file_exists(FCPATH . 'vendor/autoload.php')) {
            require_once FCPATH . 'vendor/autoload.php';
        }
    }

    /**
     * Extract plain text from a resume file
     *
     * @param string $file_path Full path to the resume file
     * @return string Extracted plain text
     */
    public function extract_text($file_path)
    {
        if (!file_exists($file_path)) {
            log_message('error', "ATS Parser: File not found - {$file_path}");
            return '';
        }

        $extension = strtolower(pathinfo($file_path, PATHINFO_EXTENSION));
        $text = '';

        try {
            switch ($extension) {
                case 'pdf':
                    $text = $this->extract_pdf($file_path);
                    break;
                case 'docx':
                    $text = $this->extract_docx($file_path);
                    break;
                case 'doc':
                    $text = $this->extract_doc_fallback($file_path);
                    break;
                case 'txt':
                    $text = file_get_contents($file_path);
                    break;
                default:
                    log_message('warning', "ATS Parser: Unsupported file type - {$extension}");
                    $text = '';
                    break;
            }
        } catch (Exception $e) {
            log_message('error', "ATS Parser: Extraction failed - " . $e->getMessage());
            $text = '';
        }

        return trim($text);
    }

    /**
     * Extract text from PDF using smalot/pdfparser
     */
    private function extract_pdf($file_path)
    {
        if (!class_exists('\Smalot\PdfParser\Parser')) {
            log_message('error', 'ATS Parser: PDFParser library not installed');
            return '';
        }

        $parser = new \Smalot\PdfParser\Parser();
        $pdf = $parser->parseFile($file_path);
        return $pdf->getText();
    }

    /**
     * Extract text from DOCX using PHPWord
     */
    private function extract_docx($file_path)
    {
        if (!class_exists('\PhpOffice\PhpWord\IOFactory')) {
            log_message('error', 'ATS Parser: PHPWord library not installed');
            return '';
        }

        $phpWord = \PhpOffice\PhpWord\IOFactory::load($file_path);
        $text = '';

        foreach ($phpWord->getSections() as $section) {
            foreach ($section->getElements() as $element) {
                if (method_exists($element, 'getText')) {
                    $textContent = $element->getText();
                    if (is_string($textContent)) {
                        $text .= $textContent . ' ';
                    }
                } elseif (method_exists($element, 'getElements')) {
                    foreach ($element->getElements() as $child) {
                        if (method_exists($child, 'getText')) {
                            $textContent = $child->getText();
                            if (is_string($textContent)) {
                                $text .= $textContent . ' ';
                            }
                        }
                    }
                }
            }
        }

        return $text;
    }

    /**
     * Fallback for .doc files (try to read as binary text)
     */
    private function extract_doc_fallback($file_path)
    {
        // Old .doc format is complex; try basic string extraction
        $content = file_get_contents($file_path);
        // Strip binary characters, keep printable ASCII and common unicode
        $text = preg_replace('/[^a-zA-Z0-9\s\.\,\;\:\!\?\-\(\)\/\&]/', ' ', $content);
        return preg_replace('/\s+/', ' ', $text);
    }

    /**
     * Analyze resume text against required skills and calculate ATS score
     *
     * @param string $resume_text Plain text extracted from resume
     * @param array $required_skills Array of objects with skill_id, skill_name, is_mandatory
     * @return array ['ats_score', 'matched_skills', 'missing_skills', 'skill_match_details', 'resume_text']
     */
    public function analyze_resume($resume_text, $required_skills)
    {
        if (empty($resume_text) || empty($required_skills)) {
            return [
                'ats_score' => 0.00,
                'matched_skills' => [],
                'missing_skills' => array_map(function($s) { return $s->skill_name; }, $required_skills),
                'skill_match_details' => [],
                'resume_text' => $resume_text
            ];
        }

        // Normalize resume text
        $normalized_text = $this->normalize_text($resume_text);

        $matched = [];
        $missing = [];
        $score_details = [];

        foreach ($required_skills as $skill) {
            $skill_name = $skill->skill_name;
            $skill_lower = strtolower($skill_name);
            $is_mandatory = (bool) $skill->is_mandatory;

            // Direct match
            $direct_match = $this->find_direct_match($skill_lower, $normalized_text);

            // Variant match
            $variant_match = $this->find_variant_match($skill_lower, $normalized_text);

            if ($direct_match || $variant_match) {
                $matched[] = $skill_name;
                $score_details[$skill_name] = [
                    'matched' => true,
                    'mandatory' => $is_mandatory,
                    'method' => $direct_match ? 'direct' : 'variant'
                ];
            } else {
                $missing[] = $skill_name;
                $score_details[$skill_name] = [
                    'matched' => false,
                    'mandatory' => $is_mandatory,
                    'method' => 'none'
                ];
            }
        }

        // Calculate weighted score
        $ats_score = $this->calculate_score($required_skills, $matched, $missing);

        return [
            'ats_score' => round($ats_score, 2),
            'matched_skills' => $matched,
            'missing_skills' => $missing,
            'skill_match_details' => $score_details,
            'resume_text' => $resume_text
        ];
    }

    /**
     * Normalize text: lowercase, remove special chars, remove stop words
     */
    private function normalize_text($text)
    {
        // Lowercase
        $text = strtolower($text);

        // Replace special characters with spaces (keep alphanumeric, dots, plus, hash, slashes)
        $text = preg_replace('/[^a-z0-9\s\.\+#\/]/', ' ', $text);

        // Normalize whitespace
        $text = preg_replace('/\s+/', ' ', $text);

        return trim($text);
    }

    /**
     * Find direct match of skill name in text
     */
    private function find_direct_match($skill_lower, $text)
    {
        // Check for the exact skill name as a word boundary match
        $pattern = '/\b' . preg_quote($skill_lower, '/') . '\b/i';
        return preg_match($pattern, $text) === 1;
    }

    /**
     * Find variant match using skill variant mappings
     */
    private function find_variant_match($skill_lower, $text)
    {
        if (!isset($this->skill_variants[$skill_lower])) {
            return false;
        }

        foreach ($this->skill_variants[$skill_lower] as $variant) {
            $pattern = '/\b' . preg_quote($variant, '/') . '\b/i';
            if (preg_match($pattern, $text) === 1) {
                return true;
            }
        }

        return false;
    }

    /**
     * Calculate weighted ATS score
     * Mandatory skills = 70% weight, Preferred skills = 30% weight
     */
    private function calculate_score($required_skills, $matched, $missing)
    {
        $total_mandatory = 0;
        $total_preferred = 0;
        $matched_mandatory = 0;
        $matched_preferred = 0;

        foreach ($required_skills as $skill) {
            if ((bool) $skill->is_mandatory) {
                $total_mandatory++;
                if (in_array($skill->skill_name, $matched)) {
                    $matched_mandatory++;
                }
            } else {
                $total_preferred++;
                if (in_array($skill->skill_name, $matched)) {
                    $matched_preferred++;
                }
            }
        }

        $mandatory_score = ($total_mandatory > 0) ? ($matched_mandatory / $total_mandatory) * 70 : 0;
        $preferred_score = ($total_preferred > 0) ? ($matched_preferred / $total_preferred) * 30 : 0;

        return $mandatory_score + $preferred_score;
    }

    /**
     * Get ATS score badge HTML based on score
     */
    public function get_score_badge($score)
    {
        if ($score >= 80) {
            return '<span class="label label-success" style="font-size:13px;padding:5px 10px;">' . number_format($score, 1) . '% - Excellent</span>';
        } elseif ($score >= 50) {
            return '<span class="label label-warning" style="font-size:13px;padding:5px 10px;">' . number_format($score, 1) . '% - Good</span>';
        } elseif ($score > 0) {
            return '<span class="label label-danger" style="font-size:13px;padding:5px 10px;">' . number_format($score, 1) . '% - Low</span>';
        } else {
            return '<span class="label label-default" style="font-size:13px;padding:5px 10px;" title="No skills configured for this job">0% - Pending</span>';
        }
    }

    /**
     * Get ATS score progress bar HTML
     */
    public function get_score_progress($score)
    {
        if ($score >= 80) {
            $color = 'progress-bar-success';
        } elseif ($score >= 50) {
            $color = 'progress-bar-warning';
        } else {
            $color = 'progress-bar-danger';
        }

        return '<div class="progress" style="margin-bottom:5px;height:20px;">
            <div class="progress-bar ' . $color . '" style="width:' . $score . '%;line-height:20px;font-size:12px;">' . number_format($score, 1) . '%</div>
        </div>';
    }
}
