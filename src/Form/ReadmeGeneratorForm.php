<?php

namespace Drupal\corillabase_makedoc\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Dotenv\Dotenv;
use Orhanerday\OpenAi\OpenAi;

/**
 * Provides a README Generator form.
 */
class ReadmeGeneratorForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'site_readme_generator_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['actions']['#type'] = 'actions';
    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Generate README'),
      '#button_type' => 'primary',
    ];

    return $form;
  }
  public function submitForm(array &$form, FormStateInterface $form_state) {

    $dotenv = Dotenv::createImmutable(__DIR__);
    $dotenv->load();
    
    $apiKey = $_ENV['OPENAI_API_KEY'];
    $client = new OpenAi($apiKey);
    
    $prompt = "Ciao AI!";
    try {
      $response = $client->chat([
          'model' => 'gpt-3.5-turbo', 
          'messages' => [
              [
                  "role" => "system",
                  "content" => $prompt
              ]
          ],
          'temperature' => 0.7,
          'max_tokens' => 2048,
      ]);

        $responseDecode = json_decode($response);
        $responseString = json_encode($responseDecode, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        $content = $responseDecode->choices[0]->message->content;

        \Drupal::messenger()->addMessage($this->t('AI Response: @response', ['@response' => $content]));

    } catch (\Exception $e) {
        \Drupal::messenger()->addMessage($this->t('Error generating analysis: @error', ['@error' => $e->getMessage()]), 'error');
    }
}

}

  
