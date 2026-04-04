<?php

namespace Ahy\EstateApiIntegration\ViewModel;

use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\Checkout\Model\Session;
use Ahy\EstateApiIntegration\Logger\Logger;

class CheckoutRestrictions implements ArgumentInterface
{
    public function __construct(
        private Session $checkoutSession,
        private Logger $logger
    ) {}

    public function hasAgeRestriction(): bool
    {
        $quote = $this->checkoutSession->getQuote();

        if (!$quote || !$quote->getId()) {
            $this->logger->warning('[Estate][AgeRestriction] No active quote');
            return false;
        }

        $raw = $quote->getData('orchid_restriction_level');

        $this->logger->info('[Estate][AgeRestriction] Raw value', [
            'quote_id' => $quote->getId(),
            'raw'      => $raw
        ]);

        if (empty($raw) || is_numeric($raw)) {
            return false;
        }

        try {
            $data = json_decode($raw, true, 512, JSON_THROW_ON_ERROR);
        } catch (\Throwable $e) {
            $this->logger->error('[Estate][AgeRestriction] Invalid JSON', [
                'exception' => $e->getMessage(),
                'raw'       => $raw
            ]);
            return false;
        }

        return !empty($data['age_restriction']);
    }

    public function getAgeRestriction(): ?string
    {
        try {
            $quote = $this->checkoutSession->getQuote();
            
            if (!$quote || !$quote->getId()) {
                $this->logger->info('[Estate][AgeRestriction] No quote available');
                return null;
            }
            
            $raw = (string) $quote->getData('orchid_restriction_level');

            if (empty($raw)) {
                $this->logger->info('[Estate][AgeRestriction] No restriction data');
                return null;
            }

            $data = json_decode($raw, true, 512, JSON_THROW_ON_ERROR);
            
            $restriction = $data['age_restriction'] ?? null;
            
            $this->logger->info('[Estate][AgeRestriction] Restriction value', [
                'restriction' => $restriction
            ]);

            return $restriction;
        } catch (\Throwable $e) {
            $this->logger->error('[Estate][AgeRestriction] Failed to parse', [
                'exception' => $e->getMessage(),
                'raw'       => $raw ?? 'N/A'
            ]);
            return null;
        }
    }

    public function  getShippingRestriction(): ?string
    {
        try {
            $quote = $this->checkoutSession->getQuote();
            
            if (!$quote || !$quote->getId()) {
                $this->logger->info('[Estate][ShippingRestriction] No quote available');
                return null;
            }
            
            $raw = (string) $quote->getData('orchid_restriction_level');

            if (empty($raw)) {
                $this->logger->info('[Estate][ShippingRestriction] No restriction data');
                return null;
            }

            $data = json_decode($raw, true, 512, JSON_THROW_ON_ERROR);
            
            $restriction = $data['shipping_restriction'] ?? null;
            
            $this->logger->info('[Estate][ShippingRestriction] Restriction value', [
                'restriction' => $restriction
            ]);

            return $restriction;
        } catch (\Throwable $e) {
            $this->logger->error('[Estate][ShippingRestriction] Failed to parse', [
                'exception' => $e->getMessage(),
                'raw'       => $raw ?? 'N/A'
            ]);
            return null;
        }
    }
    
    /**
     * Check if document has been uploaded - VERIFIES QUOTE DATA, not just session
     *
     * @return bool
     */
    public function isDocumentUploaded(): bool
    {
        try {
            $quote = $this->checkoutSession->getQuote();
            
            // Must have active quote
            if (!$quote || !$quote->getId()) {
                $this->logger->info('[Estate][DocumentUpload] No active quote');
                $this->cleanupStaleSessionData();
                return false;
            }

            // --- SESSION MANAGEMENT LOGIC ---
            // If the quote ID in session doesn't match the current quote, clear the document session flags.
            // This ensures that when a user comes with a new quote, they must re-upload.
            $sessionQuoteId = $this->checkoutSession->getLastDocumentVerifiedQuoteId();
            if ($sessionQuoteId && $sessionQuoteId != $quote->getId()) {
                $this->logger->info('[Estate][DocumentUpload] New quote detected, clearing session flags', [
                    'old_quote_id' => $sessionQuoteId,
                    'new_quote_id' => $quote->getId()
                ]);
                $this->cleanupStaleSessionData();
            }
            // --------------------------------

            $documentPath = $quote->getData('estate_document');
            if (empty($documentPath)) {
                $this->logger->info('[Estate][DocumentUpload] No document path in quote', [
                    'quote_id' => $quote->getId()
                ]);
                // Clean up stale session data
                $this->cleanupStaleSessionData();
                return false;
            }
            
            if (!$this->checkoutSession->getDocumentUploaded()) {
                $this->checkoutSession->setDocumentUploaded(true);
                $this->checkoutSession->setLastDocumentVerifiedQuoteId($quote->getId());
                $this->logger->info('[Estate][DocumentUpload] Synced session flag for quote ' . $quote->getId());
            }
            
            $this->logger->info('[Estate][DocumentUpload] Document verified', [
                'quote_id' => $quote->getId(),
                'document_path' => $documentPath
            ]);

            return true;
            
        } catch (\Exception $e) {
            $this->logger->error('[Estate][DocumentUpload] Error checking upload status', [
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }
    
    /**
     * Clean up stale session data
     */
    private function cleanupStaleSessionData(): void
    {
        $this->checkoutSession->setDocumentUploaded(false);
        $this->checkoutSession->setUploadedDocumentPath(null);
        $this->checkoutSession->setUploadedDocumentName(null);
        $this->checkoutSession->setDocumentUploadedAt(null);
        $this->checkoutSession->setLastDocumentVerifiedQuoteId(null);
        $this->logger->info('[Estate][DocumentUpload] Cleaned stale session data');
    }

    /**
     * Check if document upload is required for current quote
     *
     * @return bool
     */
    public function isDocumentUploadRequired(): bool
    {
        try {
            $ageRestriction = $this->getAgeRestriction();
            $isRequired = $ageRestriction === 'ADE' || $ageRestriction === 'SNJ' || $ageRestriction === 'BB';

            $this->logger->info('[Estate][DocumentUpload] Document upload requirement check', [
                'age_restriction' => $ageRestriction,
                'is_required' => $isRequired,
                'quote_id' => $this->checkoutSession->getQuote() ? $this->checkoutSession->getQuote()->getId() : 'N/A'
            ]);

            return $isRequired;
        } catch (\Exception $e) {
            $this->logger->error('[Estate][DocumentUpload] Error checking requirement', [
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Get uploaded document information
     *
     * @return array|null
     */
    public function getUploadedDocumentInfo(): ?array
    {
        if (!$this->isDocumentUploaded()) {
            return null;
        }

        return [
            'path' => $this->checkoutSession->getUploadedDocumentPath(),
            'name' => $this->checkoutSession->getUploadedDocumentName(),
            'uploaded_at' => $this->checkoutSession->getDocumentUploadedAt()
        ];
    }

    /**
     * Get the compliance message from the quote data
     *
     * @return string|null
     */
    public function getComplianceMessage(): ?string
    {
        try {
            $quote = $this->checkoutSession->getQuote();
            if (!$quote || !$quote->getId()) {
                return null;
            }

            $raw = (string) $quote->getData('orchid_restriction_level');
            if (empty($raw)) {
                return null;
            }

            $data = json_decode($raw, true, 512, JSON_THROW_ON_ERROR);
            
            // Return the message from the quote data if available
            return $data['compliance_message'] ?? null;
        } catch (\Throwable $e) {
            return null;
        }
    }
}