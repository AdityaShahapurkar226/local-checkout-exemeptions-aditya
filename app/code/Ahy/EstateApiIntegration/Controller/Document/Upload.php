<?php
declare(strict_types=1);

namespace Ahy\EstateApiIntegration\Controller\Document;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\CsrfAwareActionInterface;
use Magento\Framework\App\Request\InvalidRequestException;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\MediaStorage\Model\File\UploaderFactory;
use Magento\Framework\Filesystem;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Quote\Api\CartRepositoryInterface;  // ADD THIS
use Ahy\EstateApiIntegration\Logger\Logger;

class Upload extends Action implements CsrfAwareActionInterface
{
    public function __construct(
        Context $context,
        private JsonFactory $jsonFactory,
        private UploaderFactory $uploaderFactory,
        private Filesystem $filesystem,
        private CheckoutSession $checkoutSession,
        private Logger $logger,
        private CartRepositoryInterface $quoteRepository  // ADD THIS
    ) {
        parent::__construct($context);
    }

    public function execute()
    {
        $result = $this->jsonFactory->create();

        $this->logger->info('=== Upload Controller Execute ===');

        try {
            if (empty($_FILES)) {
                throw new \Exception('No files uploaded.');
            }

            // Determine uploaded file key
            $fileKey = isset($_FILES['document']) && $_FILES['document']['tmp_name'] ? 'document' : array_key_first($_FILES);
            if (!$fileKey || !isset($_FILES[$fileKey])) {
                throw new \Exception('No document uploaded.');
            }

            $fileData = $_FILES[$fileKey];

            if ($fileData['error'] !== UPLOAD_ERR_OK) {
                $errorMessages = [
                    UPLOAD_ERR_INI_SIZE => 'The uploaded file exceeds the upload_max_filesize directive in php.ini',
                    UPLOAD_ERR_FORM_SIZE => 'The uploaded file exceeds the MAX_FILE_SIZE directive (5MB)',
                    UPLOAD_ERR_PARTIAL => 'The uploaded file was only partially uploaded',
                    UPLOAD_ERR_NO_FILE => 'No file was uploaded',
                    UPLOAD_ERR_NO_TMP_DIR => 'Missing a temporary folder',
                    UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk',
                    UPLOAD_ERR_EXTENSION => 'A PHP extension stopped the file upload',
                ];
                $errorMsg = $errorMessages[$fileData['error']] ?? 'Unknown upload error';
                throw new \Exception($errorMsg);
            }

            if (empty($fileData['tmp_name']) || !is_uploaded_file($fileData['tmp_name'])) {
                throw new \Exception('Invalid file upload.');
            }

            if ($fileData['size'] <= 0) {
                throw new \Exception('Uploaded file is empty.');
            }

            // Check file size (5MB max)
            $maxFileSize = 5 * 1024 * 1024;
            if ($fileData['size'] > $maxFileSize) {
                throw new \Exception('File size must be less than 5MB.');
            }

            // Allowed extensions & MIME types
            $allowedExtensions = ['pdf', 'jpg', 'jpeg', 'png', 'webp'];
            $allowedMimeTypes  = [
                'image/jpeg',
                'image/png',
                'image/webp',
                'image/jpg',
                'application/pdf'
            ];

            // Validate extension
            $extension = strtolower(pathinfo($fileData['name'], PATHINFO_EXTENSION));
            if (!in_array($extension, $allowedExtensions)) {
                throw new \Exception('Invalid file extension. Allowed: PDF, JPG, PNG, WebP.');
            }

            // Create uploader
            $uploader = $this->uploaderFactory->create(['fileId' => $fileKey]);
            $uploader->setAllowedExtensions($allowedExtensions);
            $uploader->setAllowRenameFiles(true);
            $uploader->setFilesDispersion(false);
            $uploader->setAllowCreateFolders(true);

            // Save file
            $mediaDir = $this->filesystem->getDirectoryWrite(DirectoryList::MEDIA);
            $uploadPath = 'estate_uploads/documents';
            $uploadResult = $uploader->save($mediaDir->getAbsolutePath($uploadPath));

            if (!$uploadResult || empty($uploadResult['file'])) {
                throw new \Exception('File upload failed.');
            }

            $fileName = ltrim($uploadResult['file'], '/');
            $fileFullPath = $mediaDir->getAbsolutePath($uploadPath . '/' . $fileName);

            // Validate MIME type
            $finfo = new \finfo(FILEINFO_MIME_TYPE);
            $mimeType = $finfo->file($fileFullPath);

            if (!in_array($mimeType, $allowedMimeTypes)) {
                throw new \Exception('Invalid file type. Please upload PDF, JPG, PNG, or WebP files only.');
            }

            // Get quote
            $quote = $this->checkoutSession->getQuote();
            if (!$quote || !$quote->getId()) {
                throw new \Exception('No active quote found.');
            }

            $uploadedAt = date('Y-m-d H:i:s');
            $relativePath = $uploadPath . '/' . $fileName;

            // CRITICAL: Save to quote using Repository (reliable)
            $quote->setData('estate_document', $relativePath);
            $this->quoteRepository->save($quote);
            
            // Verify save worked
            $verifyQuote = $this->quoteRepository->get($quote->getId());
            if (empty($verifyQuote->getData('estate_document'))) {
                throw new \Exception('Failed to save document reference to quote.');
            }

            // Store in session
            $this->checkoutSession->setDocumentUploaded(true);
            $this->checkoutSession->setUploadedDocumentPath($relativePath);
            $this->checkoutSession->setUploadedDocumentName($uploadResult['name']);
            $this->checkoutSession->setDocumentUploadedAt($uploadedAt);

            $this->logger->info('Estate document uploaded successfully', [
                'quote_id' => $quote->getId(),
                'file' => $relativePath,
                'filename' => $uploadResult['name'],
                'verified' => true
            ]);

            return $result->setData([
                'success' => true,
                'file' => $relativePath,
                'filename' => $uploadResult['name'],
                'message' => 'Document uploaded successfully. You can now proceed to the next step.'
            ]);

        } catch (\Exception $e) {
            $this->logger->error('Estate document upload failed', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return $result->setData([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }

    public function createCsrfValidationException(RequestInterface $request): ?InvalidRequestException
    {
        return null;
    }

    public function validateForCsrf(RequestInterface $request): ?bool
    {
        return true;
    }
}