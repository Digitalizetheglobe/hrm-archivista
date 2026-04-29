<?php

namespace App\Http\Controllers;

use App\Models\LetterTemplate;
use App\Models\GeneratedLetter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;
use PDF;

class LetterTemplateController extends Controller
{
    public function index()
    {
        if (\Auth::user()->type != 'company') {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $letterTemplates = LetterTemplate::where('created_by', \Auth::user()->id)->get();
        return view('letter_templates.index', compact('letterTemplates'));
    }

    public function create()
    {
        if (\Auth::user()->type != 'company') {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        // Get available letters from the folder (PDF files only)
        $lettersPath = public_path('assets/letters');
        $letters = [];
        
        if (File::exists($lettersPath)) {
            $files = File::files($lettersPath);
            foreach ($files as $file) {
                if ($file->getExtension() === 'pdf') {
                    $letters[] = $file->getFilenameWithoutExtension();
                }
            }
        }

        return view('letter_templates.create', compact('letters'));
    }

    public function store(Request $request)
    {
        if (\Auth::user()->type != 'company') {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $validator = \Validator::make(
            $request->all(),
            [
                'name' => 'required|string|max:255',
                'content' => 'required|string',
            ]
        );

        if ($validator->fails()) {
            $messages = $validator->getMessageBag();
            return redirect()->back()->with('error', $messages->first());
        }

        $letterTemplate = new LetterTemplate();
        $letterTemplate->name = $request->name;
        $letterTemplate->content = $request->content;
        $letterTemplate->source_letter = $request->source_letter ?? null;
        $letterTemplate->created_by = \Auth::user()->id;
        $letterTemplate->save();

        return redirect()->route('letter_templates.index')->with('success', __('Letter template successfully created.'));
    }

    public function edit($id)
    {
        if (\Auth::user()->type != 'company') {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $letterTemplate = LetterTemplate::where('id', $id)->where('created_by', \Auth::user()->id)->first();
        
        if (!$letterTemplate) {
            return redirect()->back()->with('error', __('Letter template not found.'));
        }

        // Get available letters from folder (PDF files only)
        $lettersPath = public_path('assets/letters');
        $letters = [];
        
        if (File::exists($lettersPath)) {
            $files = File::files($lettersPath);
            foreach ($files as $file) {
                if ($file->getExtension() === 'pdf') {
                    $letters[] = $file->getFilenameWithoutExtension();
                }
            }
        }

        return view('letter_templates.edit', compact('letterTemplate', 'letters'));
    }

    public function update(Request $request, $id)
    {
        if (\Auth::user()->type != 'company') {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $validator = \Validator::make(
            $request->all(),
            [
                'name' => 'required|string|max:255',
                'content' => 'required|string',
            ]
        );

        if ($validator->fails()) {
            $messages = $validator->getMessageBag();
            return redirect()->back()->with('error', $messages->first());
        }

        $letterTemplate = LetterTemplate::where('id', $id)->where('created_by', \Auth::user()->id)->first();
        
        if (!$letterTemplate) {
            return redirect()->back()->with('error', __('Letter template not found.'));
        }

        $letterTemplate->name = $request->name;
        $letterTemplate->content = $request->content;
        $letterTemplate->source_letter = $request->source_letter ?? null;
        $letterTemplate->save();

        return redirect()->route('letter_templates.index')->with('success', __('Letter template successfully updated.'));
    }

    public function destroy($id)
    {
        if (\Auth::user()->type != 'company') {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $letterTemplate = LetterTemplate::where('id', $id)->where('created_by', \Auth::user()->id)->first();
        
        if (!$letterTemplate) {
            return redirect()->back()->with('error', __('Letter template not found.'));
        }

        $letterTemplate->delete();

        return redirect()->route('letter_templates.index')->with('success', __('Letter template successfully deleted.'));
    }

    public function generateLetter($id)
    {
        if (\Auth::user()->type != 'company') {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $letterTemplate = LetterTemplate::where('id', $id)->where('created_by', \Auth::user()->id)->first();
        
        if (!$letterTemplate) {
            return redirect()->back()->with('error', __('Letter template not found.'));
        }

        // Extract variables from template content
        preg_match_all('/\{([^}]+)\}/', $letterTemplate->content, $matches);
        $variables = $matches[1];

        return view('letter_templates.generate', compact('letterTemplate', 'variables'));
    }

    public function generatePdf(Request $request, $id)
    {
        if (\Auth::user()->type != 'company') {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $letterTemplate = LetterTemplate::where('id', $id)->where('created_by', \Auth::user()->id)->first();
        
        if (!$letterTemplate) {
            return redirect()->back()->with('error', __('Letter template not found.'));
        }

        // Clean up old PDFs (older than 1 hour)
        $this->cleanupOldPdfs();

        // Replace variables in template
        $content = $letterTemplate->content;
        $variablesData = [];
        foreach ($request->except(['_token']) as $key => $value) {
            $content = str_replace('{' . $key . '}', $value, $content);
            $variablesData[$key] = $value;
        }

        // Generate PDF
        $pdf = PDF::loadHTML($content);
        $pdf->setPaper('A4', 'portrait');
        
        // Store PDF temporarily for preview
        $safeTemplateName = preg_replace('/[^a-zA-Z0-9_]/', '_', $letterTemplate->name);
        $filename = 'letter_' . $safeTemplateName . '_' . time() . '.pdf';
        $filePath = 'temp_pdfs/' . $filename;
        $fullPath = public_path($filePath);
        
        // Ensure directory exists
        if (!file_exists(public_path('temp_pdfs'))) {
            mkdir(public_path('temp_pdfs'), 0755, true);
        }
        
        // Save PDF to temporary location
        file_put_contents($fullPath, $pdf->output());
        
        // Save record to database
        $generatedLetter = GeneratedLetter::create([
            'letter_template_id' => $letterTemplate->id,
            'created_by' => \Auth::user()->id,
            'recipient_name' => $variablesData['employee_name'] ?? 'Unknown',
            'recipient_email' => $variablesData['email'] ?? null,
            'recipient_department' => $variablesData['department'] ?? null,
            'letter_date' => $variablesData['date'] ?? now(),
            'variables_data' => $variablesData,
            'file_path' => $filePath,
            'file_name' => 'letter_' . $letterTemplate->name . '.pdf',
        ]);
        
        // Return JSON response with download URL
        return response()->json([
            'success' => true,
            'download_url' => route('letter_templates.downloadPdf', ['filename' => $filename]),
            'filename' => 'letter_' . $letterTemplate->name . '.pdf'
        ]);
    }

    private function cleanupOldPdfs()
    {
        $tempDir = public_path('temp_pdfs');
        if (!file_exists($tempDir)) {
            return;
        }

        $files = glob($tempDir . '/*.pdf');
        $currentTime = time();
        
        foreach ($files as $file) {
            if (filemtime($file) < ($currentTime - 3600)) { // 1 hour ago
                unlink($file);
            }
        }
    }

    public function downloadPdf($filename)
    {
        if (\Auth::user()->type != 'company') {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $filePath = public_path('temp_pdfs/' . $filename);
        
        if (!file_exists($filePath)) {
            return redirect()->back()->with('error', __('PDF file not found.'));
        }

        // Clean filename for download (remove timestamp)
        $cleanFilename = preg_replace('/_\d+\.pdf$/', '.pdf', $filename);
        
        return response()->download($filePath, $cleanFilename);
    }

    public function loadLetterContent(Request $request)
    {
        $letterName = $request->letter_name;
        $pdfPath = public_path('assets/letters/' . $letterName . '.pdf');
        
        if (!File::exists($pdfPath)) {
            return response()->json(['error' => 'PDF file not found.'], 404);
        }

        try {
            // Use Laravel's built-in PDF parser to extract text
            $parser = new \Smalot\PdfParser\Parser();
            $pdf = $parser->parseFile($pdfPath);
            $content = $pdf->getText();
            
            // Convert plain text to HTML for Summernote editor
            $content = nl2br(htmlspecialchars($content));
            
            return response()->json(['content' => $content]);
            
        } catch (\Exception $e) {
            // Fallback if PDF parsing fails
            return response()->json([
                'content' => '<h3>Content from: ' . $letterName . '</h3><p>PDF content could not be loaded automatically. Please add your content manually.</p>'
            ]);
        }
    }

    public function generatedLettersIndex(Request $request)
    {
        if (\Auth::user()->type != 'company') {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $query = GeneratedLetter::where('created_by', \Auth::user()->id)
            ->with('letterTemplate');

        // Filter by letter template if selected
        if ($request->filled('letter_template_id')) {
            $query->where('letter_template_id', $request->letter_template_id);
        }

        $generatedLetters = $query->latest()->get();

        // Get letter templates that have generated letters for filter
        $letterTemplatesWithGenerated = LetterTemplate::whereHas('generatedLetters', function($q) {
            $q->where('created_by', \Auth::user()->id);
        })->pluck('name', 'id');

        return view('letter_templates.generated_index', compact('generatedLetters', 'letterTemplatesWithGenerated'));
    }

    public function viewGeneratedLetter($id)
    {
        if (\Auth::user()->type != 'company') {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $generatedLetter = GeneratedLetter::where('id', $id)
            ->where('created_by', \Auth::user()->id)
            ->first();

        if (!$generatedLetter) {
            return redirect()->back()->with('error', __('Generated letter not found.'));
        }

        $filePath = public_path($generatedLetter->file_path);
        
        if (!file_exists($filePath)) {
            return redirect()->back()->with('error', __('PDF file not found.'));
        }

        return response()->file($filePath, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="' . $generatedLetter->file_name . '"'
        ]);
    }

    public function deleteGeneratedLetter($id)
    {
        if (\Auth::user()->type != 'company') {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $generatedLetter = GeneratedLetter::where('id', $id)
            ->where('created_by', \Auth::user()->id)
            ->first();

        if (!$generatedLetter) {
            return redirect()->back()->with('error', __('Generated letter not found.'));
        }

        // Delete physical file
        $filePath = public_path($generatedLetter->file_path);
        if (file_exists($filePath)) {
            unlink($filePath);
        }

        // Delete database record
        $generatedLetter->delete();

        return redirect()->route('letter_templates.generated.index')->with('success', __('Generated letter successfully deleted.'));
    }
}
