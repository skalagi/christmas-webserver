# Simple test for NeoPixels on Raspberry Pi
import time
import board
import neopixel


# Choose an open pin connected to the Data In of the NeoPixel strip, i.e. board.D18
# NeoPixels must be connected to D10, D12, D18 or D21 to work.
pixel_pin = board.D12

# The number of NeoPixels
num_pixels = 16

# The order of the pixel colors - RGB or GRB. Some NeoPixels have red and green reversed!
# For RGBW NeoPixels, simply change the ORDER to RGBW or GRBW.
ORDER = neopixel.GRB

pixels = neopixel.NeoPixel(pixel_pin, num_pixels, brightness=0.2, auto_write=False,
                           pixel_order=ORDER)


def strip(length, start, color, background):

    end = start+length-1
    for j in range(num_pixels):
        if(j<start or j>end):
            pixels[j] = background
        else:
            pixels[j] = color

def getBackground(backgroundIndex):
    if backgroundIndex == 0:
        return (0, 0, 0)
    if backgroundIndex == 1:
        return (30, 30, 30)
#    if backgroundIndex == 2:
#        return (230, 230, 230)

def readColorFromFile():
    file = open("../current.color", "r")
    r,g,b = file.read().split(",")
    return (r, g, b)

while True:
    for bIndex in range(0, 2):
        for j in range(1, 8):
            for i in range(num_pixels):
                strip(j, i, readColorFromFile(), getBackground(bIndex))
                pixels.show()
                time.sleep(.1)
