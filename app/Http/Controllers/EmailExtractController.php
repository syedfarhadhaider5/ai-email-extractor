<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use OpenAI;
use League\Csv\Writer; // Import the Writer class
use Symfony\Component\HttpFoundation\StreamedResponse;

class EmailExtractController extends Controller
{
    public function index(){
        return view('welcome');
    }

    public function createCsv(Request $request){
        // Check if file is uploaded and it's an .rtf file
        if ($request->hasFile('file') && $request->file('file')->getClientOriginalExtension() === 'rtf') {
            $fileContent = strip_tags(file_get_contents($request->file('file')->getRealPath()));

            // Your OpenAI API key
            $apiKey =  env('OPEN_AI_KEY');

            // Prompt provided by the user
            $prompt = "You've received an email from a startup company pitching for potential investment. The goal is to extract key details from the email content. Utilize the OpenAI API to process the email content and extract the following information:
                1. CompanyName: The name of the startup company getting pitched in the email.
                2. CompanyDomain: You have an email text containing information about various startup companies. Your task is to identify and extract the domain of the startup company mentioned. The domain may start with 'www.' or include the startup company's name followed by a common extension such as .com, .io, .org, .net, .co, .ai, or any other extension. If the domain is found in the email, provide the domain name. Additionally, if the domain closely matches the startup company's name, such as sharing the same initial words, provide that domain name.
                3. CompanyEmailURL: Generate a prompt tailored for an API tasked with analyzing input text to identify and extract a company email URL. When the input text contains a company email URL, the API should display the suggested link for further information.
                4. CompanyDeckURL: **Pitch Deck URL Retrieval**\n\nOur AI-powered system specializes in extracting pitch deck URLs from emails. Forward the email containing the pitch deck link to [email address], and our system will swiftly retrieve it for you. If no URL is found, we'll promptly inform you. Streamline your workflow with our efficient pitch deck retrieval service powered by cutting-edge AI technology.
                5. CompanyDescription: A concise overview of the company extracted description from the email content and, when accessible, its website.
                6. CompanyIndustries: A list of industries that the startup company operates in, inferred from the email.
                6. CompanyIndustries: Identified sectors in which the startup operates, deduced from the email content.
                7. CompanyLocation: The specific city, state, and country housing the startup's main office.
                8. CompanyStage: The developmental stage of the company's investment, such as seed, Series A, Series B, etc., if provided.
                9. SenderEmail: Extract the exact sender email from the solicitation. If not found, leave blank.

                Ensure to extract the information accurately, considering variations in email formats and structures. Output the extracted details in a structured format for further analysis.";

            // Split the content into smaller chunks
            $chunks = str_split($fileContent, 8000);

            $responses = [];

            // Make API call to OpenAI for each chunk
            foreach ($chunks as $chunk) {
                $client = OpenAI::client($apiKey);
                $result = $client->chat()->create([
                    'model' => 'gpt-4', // Use GPT-4 model
                    'messages' => [
                        ['role' => 'system', 'content' => $prompt],
                        ['role' => 'user', 'content' => $chunk],
                    ],
                ]);

                // Extract the response
                $response = $result->choices[0]->message->content;

                $responses[] = $response;
            }

            // Combine responses into one
            $combinedResponse = implode("\n\n", $responses);

            // Extract CompanyName, CompanyDomain, CompanyEmailURL, CompanyDeckURL, CompanyDescription, CompanyIndustries, CompanyLocation, CompanyStage, and SenderEmail using regular expressions
            preg_match('/CompanyName: (.*?)(?:\r\n|\n|$)/', $combinedResponse, $matches);
            $companyName = isset($matches[1]) ? $matches[1] : "";

            preg_match('/CompanyDomain: (.*?)(?:\r\n|\n|$)/', $combinedResponse, $matches);
            $companyDomain = isset($matches[1]) ? $matches[1] : "";

            preg_match('/CompanyEmailURL: (.*?)(?:\r\n|\n|$)/', $combinedResponse, $matches);
            $companyEmailURL = isset($matches[1]) ? $matches[1] : "";

            preg_match('/CompanyDeckURL: (.*?)(?:\r\n|\n|$)/', $combinedResponse, $matches);
            $companyDeckURL = isset($matches[1]) ? $matches[1] : "";

            preg_match('/CompanyDescription: (.*?)(?:\r\n|\n|$)/', $combinedResponse, $matches);
            $companyDescription = isset($matches[1]) ? $matches[1] : "";

            preg_match('/CompanyIndustries: (.*?)(?:\r\n|\n|$)/', $combinedResponse, $matches);
            $companyIndustries = isset($matches[1]) ? $matches[1] : "";

            preg_match('/CompanyLocation: (.*?)(?:\r\n|\n|$)/', $combinedResponse, $matches);
            $companyLocation = isset($matches[1]) ? $matches[1] : "";

            preg_match('/CompanyStage: (.*?)(?:\r\n|\n|$)/', $combinedResponse, $matches);
            $companyStage = isset($matches[1]) ? $matches[1] : "";

            preg_match('/SenderEmail: (.*?)(?:\r\n|\n|$)/', $combinedResponse, $matches);
            $senderEmail = isset($matches[1]) ? $matches[1] : "";

// Prepare data for CSV
            $notFoundMessage = 'ddd';

            $data = [
                ['Name','Domain', 'Company Email', 'Deck URL', 'Description', 'Industries', 'Location', 'Company Stage', 'Sender Email'],
                [
                        $companyName ?? '',
                        $companyDomain ?? $notFoundMessage,
                        $companyEmailURL ?? $notFoundMessage,
                        $companyDeckURL ?? $notFoundMessage,
                        $companyDescription ?? '',
                        $companyIndustries ?? $notFoundMessage,
                        $companyLocation ?? $notFoundMessage,
                        $companyStage ?? $notFoundMessage,
                        $senderEmail ?? ''
                ],
            ];

            // Create CSV
            $csvWriter = Writer::createFromFileObject(new \SplTempFileObject());
            $csvWriter->insertAll($data);

            // Set response headers
            $headers = [
                'Content-Type' => 'text/csv',
                'Content-Disposition' => 'attachment; filename="extracted_email_data.csv"',
            ];

            // Prepare response
            $response = new StreamedResponse(function () use ($csvWriter) {
                echo $csvWriter;
            }, 200, $headers);

            return $response;

        } else {
            // Return error if file is not uploaded or it's not an .rtf file
            return response()->json(['error' => 'Please upload an .rtf file', 'status' => 400]);
        }
    }
}
