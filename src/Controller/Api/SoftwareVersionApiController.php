<?php

namespace App\Controller\Api;

use App\Repository\SoftwareVersionRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

class SoftwareVersionApiController extends AbstractController
{
    // HW version regex patterns
    private const PATTERN_ST = '/^CPAA_[0-9]{4}\.[0-9]{2}\.[0-9]{2}(_[A-Z]+)?$/i';
    private const PATTERN_GD = '/^CPAA_G_[0-9]{4}\.[0-9]{2}\.[0-9]{2}(_[A-Z]+)?$/i';
    private const PATTERN_LCI_CIC = '/^B_C_[0-9]{4}\.[0-9]{2}\.[0-9]{2}$/i';
    private const PATTERN_LCI_NBT = '/^B_N_G_[0-9]{4}\.[0-9]{2}\.[0-9]{2}$/i';
    private const PATTERN_LCI_EVO = '/^B_E_G_[0-9]{4}\.[0-9]{2}\.[0-9]{2}$/i';

    #[Route('/api/carplay/software/version', name: 'api_software_version', methods: ['POST'])]
    #[Route('/api2/carplay/software/version', name: 'api_software_version_alt', methods: ['POST'])]
    public function softwareDownload(
        Request $request,
        SoftwareVersionRepository $repository
    ): JsonResponse {
        // Parse input from JSON body or form data
        $data = json_decode($request->getContent(), true);
        $version = $data['version'] ?? $request->request->get('version', '');
        $hwVersion = $data['hwVersion'] ?? $request->request->get('hwVersion', '');

        // Validate required fields
        if (empty($version)) {
            return new JsonResponse(['msg' => 'Version is required'], 200);
        }

        if (empty($hwVersion)) {
            return new JsonResponse(['msg' => 'HW Version is required'], 200);
        }

        // Detect hardware type
        $hwVersionBool = false;
        $stBool = false;
        $gdBool = false;
        $isLCI = false;
        $lciHwType = '';

        if (preg_match(self::PATTERN_ST, $hwVersion)) {
            $hwVersionBool = true;
            $stBool = true;
        }

        if (preg_match(self::PATTERN_GD, $hwVersion)) {
            $hwVersionBool = true;
            $gdBool = true;
        }

        if (preg_match(self::PATTERN_LCI_CIC, $hwVersion)) {
            $hwVersionBool = true;
            $isLCI = true;
            $lciHwType = 'CIC';
            $stBool = true;
        } elseif (preg_match(self::PATTERN_LCI_NBT, $hwVersion)) {
            $hwVersionBool = true;
            $isLCI = true;
            $lciHwType = 'NBT';
            $gdBool = true;
        } elseif (preg_match(self::PATTERN_LCI_EVO, $hwVersion)) {
            $hwVersionBool = true;
            $isLCI = true;
            $lciHwType = 'EVO';
            $gdBool = true;
        }

        if (!$hwVersionBool) {
            return new JsonResponse([
                'msg' => 'There was a problem identifying your software. Contact us for help.'
            ], 200);
        }

        // Strip leading v/V from version
        if (str_starts_with(strtolower($version), 'v')) {
            $version = substr($version, 1);
        }

        // Find matching versions in database
        $softwareVersions = $repository->findByVersionAlt($version);

        foreach ($softwareVersions as $row) {
            $isLCIEntry = str_starts_with($row->getName(), 'LCI');

            // Standard HW must only match standard entries, LCI must only match LCI
            if ($isLCI !== $isLCIEntry) {
                continue;
            }

            // For LCI, also check that the hardware type (CIC/NBT/EVO) matches the entry
            if ($isLCI && stripos($row->getName(), $lciHwType) === false) {
                continue;
            }

            if ($row->isLatest()) {
                return new JsonResponse([
                    'versionExist' => true,
                    'msg' => 'Your system is upto date!',
                    'link' => '',
                    'st' => '',
                    'gd' => '',
                ], 200);
            }

            $stLink = '';
            $gdLink = '';
            if ($stBool) {
                $stLink = $row->getStLink() ?? '';
            }
            if ($gdBool) {
                $gdLink = $row->getGdLink() ?? '';
            }

            // Determine latest version message
            $latestMsg = $isLCI ? 'v3.4.4' : 'v3.3.7';

            return new JsonResponse([
                'versionExist' => true,
                'msg' => 'The latest version of software is ' . $latestMsg . ' ',
                'link' => $row->getLink() ?? '',
                'st' => $stLink,
                'gd' => $gdLink,
            ], 200);
        }

        return new JsonResponse([
            'versionExist' => false,
            'msg' => 'There was a problem identifying your software. Contact us for help.',
            'link' => '',
            'st' => '',
            'gd' => '',
        ], 200);
    }
}
