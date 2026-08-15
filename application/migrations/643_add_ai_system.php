<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_add_ai_system extends CI_Migration {

    public function up()
    {
        // ------------------------------------------------------------------
        // AI Providers
        // ------------------------------------------------------------------
        $this->db->query("CREATE TABLE IF NOT EXISTS tbl_ai_providers (
            id               INT AUTO_INCREMENT PRIMARY KEY,
            provider_code    VARCHAR(50) NOT NULL,
            provider_name    VARCHAR(100) NOT NULL,
            api_key          TEXT DEFAULT NULL,
            api_endpoint     VARCHAR(255) DEFAULT NULL,
            default_model    VARCHAR(100) DEFAULT NULL,
            available_models TEXT DEFAULT NULL,
            is_active        TINYINT(1) NOT NULL DEFAULT 0,
            is_default       TINYINT(1) NOT NULL DEFAULT 0,
            max_tokens       INT NOT NULL DEFAULT 2048,
            temperature      DECIMAL(3,2) NOT NULL DEFAULT 0.70,
            created_at       DATETIME NOT NULL,
            updated_at       DATETIME DEFAULT NULL,
            KEY idx_provider_code (provider_code),
            KEY idx_is_active (is_active)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3");

        // ------------------------------------------------------------------
        // AI Sessions
        // ------------------------------------------------------------------
        $this->db->query("CREATE TABLE IF NOT EXISTS tbl_ai_sessions (
            session_id     VARCHAR(64) NOT NULL,
            user_id        INT NOT NULL,
            title          VARCHAR(255) DEFAULT NULL,
            module_context VARCHAR(255) DEFAULT NULL,
            created_at     DATETIME NOT NULL,
            updated_at     DATETIME DEFAULT NULL,
            PRIMARY KEY (session_id),
            KEY idx_user (user_id),
            KEY idx_user_updated (user_id, updated_at)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3");

        // ------------------------------------------------------------------
        // AI Messages
        // ------------------------------------------------------------------
        $this->db->query("CREATE TABLE IF NOT EXISTS tbl_ai_messages (
            id             BIGINT AUTO_INCREMENT PRIMARY KEY,
            session_id     VARCHAR(64) NOT NULL,
            role           ENUM('system','user','assistant') NOT NULL DEFAULT 'user',
            content        LONGTEXT NOT NULL,
            provider_used  VARCHAR(50) DEFAULT NULL,
            model_used     VARCHAR(100) DEFAULT NULL,
            tokens_used    INT NOT NULL DEFAULT 0,
            created_at     DATETIME NOT NULL,
            KEY idx_session (session_id),
            KEY idx_role (role)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3");

        // ------------------------------------------------------------------
        // AI Prompt Templates
        // ------------------------------------------------------------------
        $this->db->query("CREATE TABLE IF NOT EXISTS tbl_ai_prompts (
            prompt_id        INT AUTO_INCREMENT PRIMARY KEY,
            category         VARCHAR(50) NOT NULL DEFAULT 'General',
            title            VARCHAR(150) NOT NULL,
            prompt_template  TEXT NOT NULL,
            icon             VARCHAR(100) NOT NULL DEFAULT 'fa fa-magic',
            status           TINYINT(1) NOT NULL DEFAULT 1,
            created_at       DATETIME NOT NULL,
            KEY idx_category (category),
            KEY idx_status (status)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3");

        // ------------------------------------------------------------------
        // Seed Default Providers
        // ------------------------------------------------------------------
        $providers = array(
            array(
                'provider_code'    => 'openai',
                'provider_name'    => 'OpenAI',
                'api_key'          => '',
                'api_endpoint'     => 'https://api.openai.com/v1/chat/completions',
                'default_model'    => 'gpt-4o',
                'available_models' => json_encode(array('gpt-4o', 'gpt-4o-mini', 'gpt-4-turbo', 'gpt-3.5-turbo', 'o1', 'o1-mini', 'o3-mini')),
                'is_active'        => 0,
                'is_default'       => 1,
                'max_tokens'       => 2048,
                'temperature'      => 0.70,
            ),
            array(
                'provider_code'    => 'gemini',
                'provider_name'    => 'Google Gemini',
                'api_key'          => '',
                'api_endpoint'     => 'https://generativelanguage.googleapis.com/v1beta/models/{model}:generateContent',
                'default_model'    => 'gemini-1.5-flash',
                'available_models' => json_encode(array('gemini-1.5-flash', 'gemini-1.5-pro', 'gemini-2.0-flash')),
                'is_active'        => 0,
                'is_default'       => 0,
                'max_tokens'       => 2048,
                'temperature'      => 0.70,
            ),
            array(
                'provider_code'    => 'claude',
                'provider_name'    => 'Anthropic Claude',
                'api_key'          => '',
                'api_endpoint'     => 'https://api.anthropic.com/v1/messages',
                'default_model'    => 'claude-3-5-sonnet-latest',
                'available_models' => json_encode(array('claude-3-5-sonnet-latest', 'claude-3-5-haiku-latest', 'claude-3-opus-latest')),
                'is_active'        => 0,
                'is_default'       => 0,
                'max_tokens'       => 2048,
                'temperature'      => 0.70,
            ),
            array(
                'provider_code'    => 'deepseek',
                'provider_name'    => 'DeepSeek',
                'api_key'          => '',
                'api_endpoint'     => 'https://api.deepseek.com/chat/completions',
                'default_model'    => 'deepseek-chat',
                'available_models' => json_encode(array('deepseek-chat', 'deepseek-reasoner')),
                'is_active'        => 0,
                'is_default'       => 0,
                'max_tokens'       => 2048,
                'temperature'      => 0.70,
            ),
            array(
                'provider_code'    => 'openrouter',
                'provider_name'    => 'OpenRouter',
                'api_key'          => '',
                'api_endpoint'     => 'https://openrouter.ai/api/v1/chat/completions',
                'default_model'    => 'openrouter/auto',
                'available_models' => json_encode(array('openrouter/auto', 'openai/gpt-4o', 'anthropic/claude-3.5-sonnet', 'meta-llama/llama-3.1-8b-instruct')),
                'is_active'        => 0,
                'is_default'       => 0,
                'max_tokens'       => 2048,
                'temperature'      => 0.70,
            ),
        );

        foreach ($providers as $provider) {
            $exists = $this->db->where('provider_code', $provider['provider_code'])->count_all_results('tbl_ai_providers');
            if ($exists > 0) {
                continue;
            }
            $provider['created_at'] = date('Y-m-d H:i:s');
            $this->db->insert('tbl_ai_providers', $provider);
        }

        // ------------------------------------------------------------------
        // Seed Prompt Templates
        // ------------------------------------------------------------------
        $prompts = array(
            array('category' => 'HR',          'title' => 'Job Description',       'icon' => 'fa fa-briefcase',          'prompt_template' => 'Write a professional {department} job description including responsibilities, requirements, and benefits. Keep it {tone}.'),
            array('category' => 'HR',          'title' => 'Performance Feedback',   'icon' => 'fa fa-star-o',              'prompt_template' => 'Draft constructive performance feedback for an employee who {situation}. Be specific, balanced and actionable.'),
            array('category' => 'Sales',       'title' => 'Follow-up Email',        'icon' => 'fa fa-paper-plane-o',       'prompt_template' => 'Write a polite sales follow-up email for {lead_name} regarding {context}. Keep it short and include a clear call to action.'),
            array('category' => 'Sales',       'title' => 'Win/Loss Summary',       'icon' => 'fa fa-trophy',              'prompt_template' => 'Summarize the key reasons we won or lost this deal: {context}. Highlight lessons learned and next steps.'),
            array('category' => 'Marketing',   'title' => 'Social Media Post',      'icon' => 'fa fa-share-alt',           'prompt_template' => 'Create {count} social media posts about {topic} for {platform}. Use an engaging tone and include hashtags.'),
            array('category' => 'Marketing',   'title' => 'Blog Intro',             'icon' => 'fa fa-pencil',              'prompt_template' => 'Write an engaging introduction for a blog post about {topic}. Aim for {words} words.'),
            array('category' => 'Support',     'title' => 'Support Reply',          'icon' => 'fa fa-life-ring',           'prompt_template' => 'Draft a friendly support reply addressing: {issue}. Offer a clear solution and ask for confirmation.'),
            array('category' => 'Support',     'title' => 'FAQ Answer',             'icon' => 'fa fa-question-circle-o',   'prompt_template' => 'Provide a clear, concise answer to this FAQ: {question}.'),
            array('category' => 'Development', 'title' => 'Code Review Notes',      'icon' => 'fa fa-code',                'prompt_template' => 'Review the following code and list bugs, security issues, and improvement suggestions: {code}.'),
            array('category' => 'Development', 'title' => 'Bug Report',             'icon' => 'fa fa-bug',                 'prompt_template' => 'Help me diagnose this bug. Describe: {context}. Suggest likely causes and step-by-step fixes.'),
            array('category' => 'General',     'title' => 'Rewrite',                'icon' => 'fa fa-refresh',             'prompt_template' => 'Rewrite the following text to be clearer and more professional: {text}'),
            array('category' => 'General',     'title' => 'Summarize',              'icon' => 'fa fa-compress',            'prompt_template' => 'Summarize the following text in {words} bullet points: {text}'),
            array('category' => 'General',     'title' => 'Fix Grammar',            'icon' => 'fa fa-check',               'prompt_template' => 'Fix the grammar, spelling, and punctuation in the following text, keeping the meaning the same: {text}'),
            array('category' => 'General',     'title' => 'Generate Description',   'icon' => 'fa fa-pencil-square-o',     'prompt_template' => 'Write a compelling description for {item} in {words} words.'),
        );

        foreach ($prompts as $prompt) {
            $exists = $this->db->where('title', $prompt['title'])->count_all_results('tbl_ai_prompts');
            if ($exists > 0) {
                continue;
            }
            $prompt['status']     = 1;
            $prompt['created_at'] = date('Y-m-d H:i:s');
            $this->db->insert('tbl_ai_prompts', $prompt);
        }

        // ------------------------------------------------------------------
        // Config
        // ------------------------------------------------------------------
        $exists = $this->db->where('config_key', 'ai_assistant_enabled')->get('tbl_config');
        if ($exists->num_rows() == 0) {
            $this->db->insert('tbl_config', array('config_key' => 'ai_assistant_enabled', 'value' => '0'));
        }

        // ------------------------------------------------------------------
        // Settings Menu
        // ------------------------------------------------------------------
        $menu = $this->db->where('link', 'admin/ai/settings')->get('tbl_menu')->row();
        if (empty($menu)) {
            $this->db->insert('tbl_menu', array(
                'label'  => 'ai_settings',
                'link'   => 'admin/ai/settings',
                'icon'   => 'fa fa-fw fa-magic',
                'parent' => 25,
                'sort'   => 99,
                'status' => 2,
            ));
        }
    }

    public function down()
    {
        $this->db->query("DROP TABLE IF EXISTS tbl_ai_providers");
        $this->db->query("DROP TABLE IF EXISTS tbl_ai_sessions");
        $this->db->query("DROP TABLE IF EXISTS tbl_ai_messages");
        $this->db->query("DROP TABLE IF EXISTS tbl_ai_prompts");

        $this->db->where('config_key', 'ai_assistant_enabled')->delete('tbl_config');
        $this->db->where('link', 'admin/ai/settings')->delete('tbl_menu');
    }
}
