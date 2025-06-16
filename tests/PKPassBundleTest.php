<?php declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use PKPass\PKPass;
use PKPass\PKPassBundle;
use PKPass\PKPassException;

final class PKPassBundleTest extends TestCase
{
    private function createSamplePass(): PKPass
    {
        $pass = new PKPass(__DIR__ . '/fixtures/example-certificate.p12', 'password');
        $data = [
            'description' => 'Test pass',
            'formatVersion' => 1,
            'organizationName' => 'Test Organization',
            'passTypeIdentifier' => 'pass.com.test.example',
            'serialNumber' => '12345678',
            'teamIdentifier' => 'KN44X8ZLNC',
            'barcode' => [
                'format' => 'PKBarcodeFormatQR',
                'message' => 'Test-Message',
                'messageEncoding' => 'iso-8859-1',
            ],
            'backgroundColor' => 'rgb(32,110,247)',
            'logoText' => 'Test Pass',
            'relevantDate' => date('Y-m-d\TH:i:sP')
        ];
        $pass->setData($data);
        $pass->addFile(__DIR__ . '/fixtures/icon.png');
        return $pass;
    }

    private function validateBundle($bundleContent, $expectedPassCount = 1)
    {
        // Basic string validation
        $this->assertIsString($bundleContent);
        $this->assertGreaterThan(100, strlen($bundleContent));

        // Try to read the ZIP file
        $tempName = tempnam(sys_get_temp_dir(), 'pkpasses');
        file_put_contents($tempName, $bundleContent);
        $zip = new ZipArchive();
        $res = $zip->open($tempName);
        $this->assertTrue($res, 'Invalid ZIP file.');
        $this->assertEquals($expectedPassCount, $zip->numFiles);

        // Validate that each entry is a .pkpass file
        for ($i = 0; $i < $zip->numFiles; $i++) {
            $fileName = $zip->getNameIndex($i);
            $this->assertStringEndsWith('.pkpass', $fileName);
            $this->assertStringStartsWith('pass', $fileName);
        }

        $zip->close();
        unlink($tempName);
    }

    public function testEmptyBundle()
    {
        $bundle = new PKPassBundle();
        
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Cannot create bundle with no passes. Add at least one pass before creating the bundle.');
        
        // Test that createZip method throws an exception when no passes are added
        $reflection = new ReflectionClass($bundle);
        $method = $reflection->getMethod('createZip');
        $method->setAccessible(true);
        
        $method->invoke($bundle);
    }

    public function testSaveEmptyBundle()
    {
        $bundle = new PKPassBundle();
        
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Cannot create bundle with no passes. Add at least one pass before creating the bundle.');
        
        $tempFile = tempnam(sys_get_temp_dir(), 'test_bundle') . '.pkpasses';
        $bundle->save($tempFile);
    }

    public function testAddSinglePass()
    {
        $bundle = new PKPassBundle();
        $pass = $this->createSamplePass();
        
        $bundle->add($pass);
        
        // Use reflection to access private passes property
        $reflection = new ReflectionClass($bundle);
        $property = $reflection->getProperty('passes');
        $property->setAccessible(true);
        $passes = $property->getValue($bundle);
        
        $this->assertCount(1, $passes);
        $this->assertInstanceOf(PKPass::class, $passes[0]);
    }

    public function testAddMultiplePasses()
    {
        $bundle = new PKPassBundle();
        $pass1 = $this->createSamplePass();
        $pass2 = $this->createSamplePass();
        
        $bundle->add($pass1);
        $bundle->add($pass2);
        
        // Use reflection to access private passes property
        $reflection = new ReflectionClass($bundle);
        $property = $reflection->getProperty('passes');
        $property->setAccessible(true);
        $passes = $property->getValue($bundle);
        
        $this->assertCount(2, $passes);
        $this->assertInstanceOf(PKPass::class, $passes[0]);
        $this->assertInstanceOf(PKPass::class, $passes[1]);
    }

    public function testCreateZipWithSinglePass()
    {
        $bundle = new PKPassBundle();
        $pass = $this->createSamplePass();
        $bundle->add($pass);
        
        $reflection = new ReflectionClass($bundle);
        $method = $reflection->getMethod('createZip');
        $method->setAccessible(true);
        
        $zip = $method->invoke($bundle);
        $this->assertInstanceOf(ZipArchive::class, $zip);
        
        // Verify the zip contains one pass file
        $this->assertEquals(1, $zip->numFiles);
        $this->assertEquals('pass1.pkpass', $zip->getNameIndex(0));
        
        $zip->close();
    }

    public function testCreateZipWithMultiplePasses()
    {
        $bundle = new PKPassBundle();
        $pass1 = $this->createSamplePass();
        $pass2 = $this->createSamplePass();
        
        $bundle->add($pass1);
        $bundle->add($pass2);
        
        $reflection = new ReflectionClass($bundle);
        $method = $reflection->getMethod('createZip');
        $method->setAccessible(true);
        
        $zip = $method->invoke($bundle);
        $this->assertInstanceOf(ZipArchive::class, $zip);
        $this->assertEquals(2, $zip->numFiles);
        
        $zip->close();
    }

    public function testSaveToFile()
    {
        $bundle = new PKPassBundle();
        $pass = $this->createSamplePass();
        $bundle->add($pass);
        
        $tempFile = tempnam(sys_get_temp_dir(), 'test_bundle') . '.pkpasses';
        
        try {
            $bundle->save($tempFile);
            
            $this->assertFileExists($tempFile);
            $content = file_get_contents($tempFile);
            $this->validateBundle($content, 1);
        } finally {
            if (file_exists($tempFile)) {
                unlink($tempFile);
            }
        }
    }

    public function testSaveMultiplePassesToFile()
    {
        $bundle = new PKPassBundle();
        $pass1 = $this->createSamplePass();
        $pass2 = $this->createSamplePass();
        
        $bundle->add($pass1);
        $bundle->add($pass2);
        
        $tempFile = tempnam(sys_get_temp_dir(), 'test_bundle') . '.pkpasses';
        
        try {
            $bundle->save($tempFile);
            
            $this->assertFileExists($tempFile);
            $content = file_get_contents($tempFile);
            $this->validateBundle($content, 2);
        } finally {
            if (file_exists($tempFile)) {
                unlink($tempFile);
            }
        }
    }

    public function testSaveToInvalidPath()
    {
        $bundle = new PKPassBundle();
        $pass = $this->createSamplePass();
        $bundle->add($pass);
        
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Could not write zip archive to file.');
        
        // Try to save to a directory that doesn't exist
        $bundle->save('/nonexistent/directory/test.pkpasses');
    }

    public function testOutputHeaders()
    {
        $bundle = new PKPassBundle();
        $pass = $this->createSamplePass();
        $bundle->add($pass);
        
        // Since output() calls exit, we can't test the full method
        // But we can test that it would work by checking the createZip method
        $reflection = new ReflectionClass($bundle);
        $method = $reflection->getMethod('createZip');
        $method->setAccessible(true);
        
        $zip = $method->invoke($bundle);
        $this->assertNotEmpty($zip->filename, 'Should have a filename');
        $this->assertFileExists($zip->filename, 'Zip file should exist');
        
        $zip->close();
    }

    public function testBundleNaming()
    {
        $bundle = new PKPassBundle();
        
        // Add multiple passes to test naming
        for ($i = 1; $i <= 3; $i++) {
            $pass = $this->createSamplePass();
            $bundle->add($pass);
        }
        
        $tempFile = tempnam(sys_get_temp_dir(), 'test_bundle') . '.pkpasses';
        
        try {
            $bundle->save($tempFile);
            
            // Open the bundle and check the pass names
            $zip = new ZipArchive();
            $zip->open($tempFile);
            
            $expectedNames = ['pass1.pkpass', 'pass2.pkpass', 'pass3.pkpass'];
            for ($i = 0; $i < $zip->numFiles; $i++) {
                $fileName = $zip->getNameIndex($i);
                $this->assertEquals($expectedNames[$i], $fileName);
            }
            
            $zip->close();
        } finally {
            if (file_exists($tempFile)) {
                unlink($tempFile);
            }
        }
    }

    public function testSetTempPath()
    {
        $bundle = new PKPassBundle();
        $customTempPath = sys_get_temp_dir() . '/custom_pkpass_temp';
        
        // Create the custom temp directory
        if (!is_dir($customTempPath)) {
            mkdir($customTempPath, 0755, true);
        }
        
        try {
            $bundle->setTempPath($customTempPath);
            
            // Use reflection to verify the temp path was set
            $reflection = new ReflectionClass($bundle);
            $property = $reflection->getProperty('tempPath');
            $property->setAccessible(true);
            $actualTempPath = $property->getValue($bundle);
            
            $this->assertEquals($customTempPath, $actualTempPath);
            
            // Test that it actually uses the custom temp path by saving a bundle
            $pass = $this->createSamplePass();
            $bundle->add($pass);
            
            $tempFile = $customTempPath . '/test_bundle.pkpasses';
            $bundle->save($tempFile);
            
            $this->assertFileExists($tempFile);
            
            unlink($tempFile);
        } finally {
            // Clean up any remaining files in the custom temp directory
            $files = glob($customTempPath . '/*');
            foreach ($files as $file) {
                unlink($file);
            }
            
            // Clean up the custom temp directory
            if (is_dir($customTempPath)) {
                rmdir($customTempPath);
            }
        }
    }
}
