from PIL import Image

def extract_logo_b(input_path, output_path):
    img = Image.open(input_path)
    
    # Crop the left part (B logo)
    # The image is 1024x172. The B part is approximately a square.
    # We will crop from (0, 0) to (172, 172).
    # Actually, width in navbar is 22, height is 24.
    # 22/24 * 172 = 157.6
    # Let's crop exactly 158 width.
    box = (0, 0, 158, 172)
    b_img = img.crop(box)
    
    # Save as PNG
    b_img.save(output_path, "PNG")
    print("Created", output_path)

if __name__ == "__main__":
    extract_logo_b("public/images/logo.png", "public/favicon.png")
